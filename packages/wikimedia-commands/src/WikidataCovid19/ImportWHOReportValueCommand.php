<?php

namespace Addwiki\Commands\Wikimedia\WikidataCovid19;

use Addwiki\Commands\Wikimedia\SparqlQueryRunner;
use Addwiki\Commands\Wikimedia\WikidataReferencer\EffectiveUrlMiddleware;
use Addwiki\Topics\Covid19\WHOReports;
use ArrayAccess;
use DataValues\Deserializers\DataValueDeserializer;
use DataValues\QuantityValue;
use DataValues\Serializers\DataValueSerializer;
use DataValues\TimeValue;
use DataValues\UnboundedQuantityValue;
use DateTime;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\Guzzle\ClientFactory;
use Mediawiki\Api\MediawikiApi;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @author Addshore
 */
class ImportWHOReportValueCommand extends Command {

	private $appConfig;

	/**
	 * @var SparqlQueryRunner
	 */
	private $sparqlQueryRunner;

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var MediawikiApi
	 */
	private $wikibaseApi;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	public function initServices() {
		$clientFactory = new ClientFactory(
			array(
				'middleware' => array( EffectiveUrlMiddleware::middleware() ),
				'user-agent' => 'Addwiki - Wikidata Covid19 WHO Report Value Importer',
			)
		);
		$guzzleClient = $clientFactory->getClient();

		$this->sparqlQueryRunner = new SparqlQueryRunner( $guzzleClient );

		$this->wikibaseApi = new MediawikiApi( 'https://www.wikidata.org/w/api.php', $guzzleClient );
		$this->wikibaseFactory = new WikibaseFactory(
			$this->wikibaseApi,
			new DataValueDeserializer(
				array(
					'boolean' => 'DataValues\BooleanValue',
					'number' => 'DataValues\NumberValue',
					'string' => 'DataValues\StringValue',
					'unknown' => 'DataValues\UnknownValue',
					'globecoordinate' => 'DataValues\Geo\Values\GlobeCoordinateValue',
					'monolingualtext' => 'DataValues\MonolingualTextValue',
					'multilingualtext' => 'DataValues\MultilingualTextValue',
					'quantity' => 'DataValues\QuantityValue',
					'time' => 'DataValues\TimeValue',
					'wikibase-entityid' => 'Wikibase\DataModel\Entity\EntityIdValue',
				)
			),
			new DataValueSerializer()
		);
	}

	protected function configure() {
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'wm:wd:covid19-value' )
			->setDescription( 'Imports a value from the WHO reports regarding the Covid-19 virus' )
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured user to use',
				$defaultUser
			)
			->addOption(
				'report',
				null,
				InputOption::VALUE_OPTIONAL,
				'WHO report number'
			)
			->addOption(
				'date',
				null,
				InputOption::VALUE_OPTIONAL,
				'WHO report date eg. 20201201'
			)
			->addOption(
				'reporter',
				null,
				InputOption::VALUE_REQUIRED,
				'Reporting area / country etc'
			)
			->addOption(
				'value',
				null,
				InputOption::VALUE_REQUIRED,
				'The value to import, eg. "cases", or "deaths"'
			)->addOption(
				'dry',
				null,
				InputOption::VALUE_OPTIONAL,
				'Preform a dry run (no edits)'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		if( $input->getOption( 'dry' ) ) {
			echo "Dry run mode!" . PHP_EOL;
		}

		if(
			(!$input->getOption( 'report' ) && !$input->getOption( 'date' )) ||
			!$input->getOption( 'reporter' ) ||
			!$input->getOption( 'value' ) ){
			throw new RuntimeException( 'Missing parameter' );
		}

		$this->initServices();

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->offsetGet( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		$reportDate = $input->getOption( 'date' );
		$reportId = $input->getOption( 'report' );
		$reporter = $input->getOption( 'reporter' );
		$valueType = $input->getOption( 'value' );

		// Login...
		$loggedIn = $this->wikibaseApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			$output->writeln( 'Failed to log in' );
			return -1;
		}

		// Get the value from the report
		$whoReports = new WHOReports();
		if( $reportId ) {
			$report = $whoReports->getReportForId( $reportId );
		}elseif( $reportDate ) {
			$report = $whoReports->getReportForDate( $reportDate );
		} else {
			die( 'No way to get a report given...' );
		}
		if( $report->getId() < 46 ) {
			throw new RuntimeException( 'Doesn\'t work for reports before 46... (yet)' );
		}
		$value = $report->getValue( $reporter, $valueType );
		$date = $report->getDate();
		$date = DateTime::createFromFormat("Ymd", $date);

		echo "Report: " . $report->getId() .
			' Date: ' . $report->getDate() .
			' Reporter: ' . $reporter.
			' Value: ' . $value.
			PHP_EOL;

		// Find the report item..
		// TODO use wdqs?
		$reportSearchString = "Novel Coronavirus (2019-nCoV) Situation Report " . $report->getId();
		$searchResultIds = $this->wikibaseFactory->newEntitySearcher()->search(
			'item',
			$reportSearchString,
			'en'
		);
		if( !$searchResultIds || count($searchResultIds) > 1 ) {
			throw new RuntimeException( 'Either found no, or too many report items: ' . json_encode($searchResultIds) );
		}
		$reportItemId = new ItemId( $searchResultIds[0] );

		// Find the country outbreak info item
		// TODO use wdqs?
		// TOOD allow matching more names....
		// - 2020 coronavirus outbreak in Iran
		$locationSearchStringPrefixes = [
			"2020 coronavirus pandemic in ",
			"2020 coronavirus outbreak in ",
			"2020 coronavirus outbreak in the ",
			"2020 coronavirus pandemic in the ",
			"COVID-19 outbreak in ",
			"COVID-19 pandemic in ",
			"COVID-19 outbreak in the ",
			"COVID-19 pandemic in the ",
		];
		$locationItemIds = [];
		foreach( $locationSearchStringPrefixes as $prefix ) {
			$locationSearchString = $prefix . $reporter;
			$searchResultIds = $this->wikibaseFactory->newEntitySearcher()->search(
				'item',
				$locationSearchString,
				'en'
			);
			$locationItemIds = array_merge( $locationItemIds, $searchResultIds );
		}
		$locationItemIds = array_values( array_unique( $locationItemIds ) );
		if( !$locationItemIds || count($locationItemIds) > 1 ) {
			throw new RuntimeException(
				'Either found no, or too many location items: ' . json_encode( $locationItemIds )
			);
		}
		$locationItemId = new ItemId( $locationItemIds[0] );
		// Sandbox...
		//$locationItemId = new ItemId( 'Q4115189' );

		echo "Location Item: " . $locationItemId->getSerialization() .
			" Report Item: " . $reportItemId->getSerialization() .
			PHP_EOL;

		// Properties that we will use
		if( $valueType === 'deaths' ) {
			$valueProperty = 'P1120';
		}elseif( $valueType === 'cases' ) {
			$valueProperty = 'P1603';
		} else {
			throw new RuntimeException();
		}
		$propertyPointInTime = 'P585';
		$propertyStatedIn = 'P248';

		// What we will create
		$quantityValue = UnboundedQuantityValue::newFromNumber( $value );
		$mainSnak = new PropertyValueSnak(
			new PropertyId( $valueProperty ),
			$quantityValue
		);
		$qualifierSnak = new PropertyValueSnak(
			new PropertyId( $propertyPointInTime ),
			new TimeValue(
				'+' . $date->format( 'Y-m-d' ) . 'T00:00:00Z',
				0, 0, 0,
				TimeValue::PRECISION_DAY,
				TimeValue::CALENDAR_GREGORIAN
			) );
		$referenceSnak = new PropertyValueSnak(
			new PropertyId( $propertyStatedIn ),
			new EntityIdValue( $reportItemId )
		);

		// Check if the statement already exists, and skip if it does...
		/** @var Item $locationItem */
		$locationItem = $this->wikibaseFactory->newEntityLookup()->getEntity( $locationItemId );
		$currentStatements = $locationItem->getStatements()->getByPropertyId( new PropertyId( $valueProperty ) );
		foreach( $currentStatements->toArray() as $statement ) {
			$checkMainSnak = $statement->getMainSnak();
			if( !$checkMainSnak instanceof PropertyValueSnak ) {
				continue;
			}
			/** @var QuantityValue $checkValue */
			$checkValue = $checkMainSnak->getDataValue();
			if( $checkValue->getAmount()->equals($quantityValue->getAmount()) ) {
				echo "Value to be imported already exists in a statement on the Item" . PHP_EOL;
				return 0;
			}
		}

		// Create a new statement...
		if( $input->getOption( 'dry' ) ) {
			return 0;
		}
		// TODO maybe do this as 1 edit?
		$guid = $this->wikibaseFactory->newStatementCreator()->create( $mainSnak, $locationItemId );
		$statement = $this->wikibaseFactory->newStatementGetter()->getFromGuid( $guid );
		$statement->getQualifiers()->addSnak( $qualifierSnak );
		$statement->getReferences()->addNewReference( [ $referenceSnak ] );
		$this->wikibaseFactory->newStatementSetter()->set( $statement );

		echo "Created statement $guid" . PHP_EOL;

		return 0;
	}

}
