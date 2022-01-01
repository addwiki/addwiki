<?php

namespace Addwiki\Wikimedia\Commands;

use Addwiki\Mediawiki\Api\Client\Action\Exception\UsageException;
use Addwiki\Mediawiki\Api\Client\Auth\AuthMethod;
use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Wikibase\Api\WikibaseFactory;
use Addwiki\Wikimedia\Api\WikimediaFactory;
use ArrayAccess;
use Asparagus\QueryBuilder;
use DataValues\TimeValue;
use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;

class WikidataReferenceDateFixer extends Command {

	private ArrayAccess $appConfig;

	private WikibaseFactory $wikibaseFactory;

	private SparqlQueryRunner $sparqlQueryRunner;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	public function initServices( AuthMethod $auth ): void {
		$defaultGuzzleConf = [
			'headers' => [ 'User-Agent' => 'addwiki - Wikidata Reference Date Fixer' ]
		];
		$guzzleClient = new Client( $defaultGuzzleConf );
		$this->sparqlQueryRunner = new SparqlQueryRunner( $guzzleClient );
		$this->wikibaseFactory = ( new WikimediaFactory() )->newWikidataWikibaseFactory();
	}

	protected function configure() {
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'wm:wd:ref-retrieved-date-fix' )
			->setDescription( 'Fixes reference retrieved dates' )
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured user to use',
				$defaultUser
			)
			->addOption(
				'item',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Item to target'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->offsetGet( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		$items = $input->getOption( 'item' );

		$this->initServices( new UserAndPassword( $userDetails['username'], $userDetails['password'] ) );

		if ( empty( $items ) ) {
			$output->writeln( 'Running SPARQL query to find items to check' );
			$queryBuilder = new QueryBuilder( [
				'prov' => 'http://www.w3.org/ns/prov#',
				'wd' => 'http://www.wikidata.org/entity/',
				'wikibase' => 'http://wikiba.se/ontology#',
				'prv' => 'http://www.wikidata.org/prop/reference/value/',
			] );
			$itemIds = $this->sparqlQueryRunner->getItemIdsFromQuery(
				$queryBuilder
				->select( '?item' )
				->where( '?ref', 'prv:P813', '?value' )
				->also( '?value', 'wikibase:timeCalendarModel', 'wd:Q1985786' )
				->also( '?st', 'prov:wasDerivedFrom', '?ref' )
				->also( '?item', '?pred', '?st' )
				->limit( 10000 )
				->__toString()
			);
		} else {
			/** @var ItemId[] $itemIds */
			$itemIds = [];
			foreach ( array_unique( $items ) as $itemIdString ) {
				$itemIds[] = new ItemId( $itemIdString );
			}
		}

		$itemIds = array_unique( $itemIds );
		$output->writeln( 'Running for ' . count( $itemIds ) . ' items' );

		$itemLookup = $this->wikibaseFactory->newItemLookup();

		foreach ( $itemIds as $itemId ) {
			$output->write( $itemId->getSerialization() . ' ' );
			$item = $itemLookup->getItemForId( $itemId );

			/** Suppressions can be removed once https://github.com/wmde/WikibaseDataModel/pull/838 is released */
			/** @psalm-suppress UndefinedDocblockClass */
			/** @psalm-suppress UndefinedClass */
			foreach ( $item->getStatements()->getIterator() as $statement ) {
				foreach ( $statement->getReferences() as $reference ) {
					/** @var Reference $reference */
					foreach ( $reference->getSnaks()->getIterator() as $snak ) {
						if ( $snak instanceof PropertyValueSnak && $snak->getPropertyId()->getSerialization() == 'P813' ) {
							/** @var TimeValue $dataValue */
							$dataValue = $snak->getDataValue();
							// We can assume ALL retrieval dates should be Gregorian!
							if ( $dataValue->getCalendarModel() === TimeValue::CALENDAR_JULIAN ) {
									$oldRefHash = $reference->getHash();
									$statementGuid = $statement->getGuid();

									$snakList = $reference->getSnaks();
									$snakList = new SnakList( $snakList->getArrayCopy() );
									$snakList->removeSnak( $snak );

									$fixedTimestamp = $this->getFixedTimestamp( $dataValue->getTime() );

									if ( $fixedTimestamp ) {
										$snakList->addSnak(
											new PropertyValueSnak(
												new PropertyId( 'P813' ),
												new TimeValue(
													$fixedTimestamp,
													$dataValue->getTimezone(),
													$dataValue->getBefore(),
													$dataValue->getAfter(),
													$dataValue->getPrecision(),
													TimeValue::CALENDAR_GREGORIAN
												)
											)
										);
										$editSummary = 'Fix reference retrieval date';
										$output->write( '.' );
									} else {
										// TODO optionally remove rather than always doing so?
										$editSummary = 'Removing bad reference retrieval date';
										$output->write( 'x' );
									}

									try{
										$this->wikibaseFactory->newReferenceSetter()->set(
											new Reference( $snakList ),
											$statementGuid,
											$oldRefHash,
											new EditInfo( $editSummary )
										);
									} catch ( UsageException $usageException ) {
										$output->writeln( '' );
										$output->write( $usageException->getMessage() );
									}

							}
						}
					}
				}
			}

			$output->writeln( '' );
		}

		return 0;
	}

	/**
	 *
	 * @return string|bool false if we cant really tell how to fix this
	 */
	private function getFixedTimestamp( string $timestamp ) {
		$currentYear = date( 'Y' );
		$lastYear = ( (int)date( 'Y' ) ) - 1;

		// Try a bunch of common misstypes
		$swaps = [
			'000' . substr( $currentYear, 3, 1 ) => $currentYear,
			'00' . substr( $currentYear, 2, 2 ) => $currentYear,
			'0' . substr( $currentYear, 1, 3 ) => $currentYear,
			'0' . substr( $currentYear, 0, 1 ) . substr( $currentYear, 2, 2 ) => $currentYear,
			'000' . substr( $lastYear, 3, 1 ) => $lastYear,
			'00' . substr( $lastYear, 2, 2 ) => $lastYear,
			'0' . substr( $lastYear, 1, 3 ) => $lastYear,
			'0' . substr( $lastYear, 0, 1 ) . substr( $lastYear, 2, 2 ) => $lastYear,
		];
		foreach ( $swaps as $match => $replace ) {
			if ( strstr( $timestamp, $match ) ) {
				return str_replace( $match, $replace, $timestamp );
			}
		}

		// Also allow the last 10 years!
		$year = $currentYear;
		while ( $year >= $currentYear - 10 ) {
			if ( strstr( $timestamp, $year ) ) {
				return $timestamp;
			}

			--$year;
		}

		// Otherwise give up guessing
		return false;
	}

}
