<?php

namespace Addwiki\Commands\Wikimedia;

use ArrayAccess;
use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
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

class ExtensionToWikidata extends Command {

	private $appConfig;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->offsetGet( 'defaults.wiki' );
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'wm:exttowd' )
			->setDescription( 'Imports some Extension infomation from mediawiki.org to wikidata' )
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured user to use',
				$defaultUser
			)
			->addOption(
				'title',
				null,
				InputOption::VALUE_OPTIONAL,
				'Which title do you want to use (should be an Extension:* page)'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$user = $input->getOption( 'user' );

		$userDetails = $this->appConfig->get( 'users.' . $user );

		if( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		$pageIdentifier = null;
		if( $input->getOption( 'title' ) != null ) {
			$sourceTitle = $input->getOption( 'title' );
			$pageIdentifier = new PageIdentifier( new Title( $sourceTitle ) );
		} else {
			throw new RuntimeException( 'No titles was set!' );
		}

		$sourceApi = new MediawikiApi( "https://www.mediawiki.org/w/api.php" );
		$targetApi = new MediawikiApi( "https://www.wikidata.org/w/api.php" );
		$loggedIn = $targetApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			$output->writeln( 'Failed to log in to target wiki' );
			return -1;
		}

		$sourceMwFactory = new MediawikiFactory( $sourceApi );
		$sourceParser = $sourceMwFactory->newParser();
		$parseResult = $sourceParser->parsePage( $pageIdentifier );

		//Get the wikibase item if it exists
		$itemIdString = null;
		if( array_key_exists( 'properties', $parseResult ) ) {
			foreach( $parseResult['properties'] as $pageProp ) {
				if( $pageProp['name'] == 'wikibase_item' ) {
					$itemIdString = $pageProp['*'];
				}
			}
		}

		$targetWbFactory = new WikibaseFactory(
			$targetApi,
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

		// Create an item if there is no item yet!
		if( $itemIdString === null ) {
			$output->writeln( "Creating a new Item" );
			$item = new Item();
			$item->setLabel( 'en', $sourceTitle );
			//TODO this siteid should come from somewhere?
			$item->getSiteLinkList()->setNewSiteLink( 'mediawikiwiki', $sourceTitle );
			$targetRevSaver = $targetWbFactory->newRevisionSaver();
			$item = $targetRevSaver->save( new Revision( new Content( $item ) ) );
		} else {
			$item = $targetWbFactory->newItemLookup()->getItemForId( new ItemId( $itemIdString ) );
		}

		// Add instance of if not already there
		$hasInstanceOfExtension = false;
		foreach( $item->getStatements()->getByPropertyId( new PropertyId( 'P31' ) )->getMainSnaks() as $mainSnak ) {
			if( $mainSnak instanceof PropertyValueSnak ) {
				/** @var EntityIdValue $dataValue */
				$dataValue = $mainSnak->getDataValue();
				if( $dataValue->getEntityId()->equals( new ItemId( 'Q6805426' ) ) ) {
					$hasInstanceOfExtension = true;
					break;
				}
			}
		}
		if( !$hasInstanceOfExtension ) {
			$output->writeln( "Creating instance of Statement" );
			$targetWbFactory->newStatementCreator()->create(
				new PropertyValueSnak(
					new PropertyId( 'P31' ),
					new EntityIdValue( new ItemId( 'Q6805426' ) )
				),
				$item->getId()
			);
		}

		// Try to add a licence
		$catLicenseMap = array(
			'Public_domain_licensed_extensions' => 'Q19652',
		);
		$extensionLicenseItemIdString = null;
		if( array_key_exists( 'categories', $parseResult ) ) {
			foreach( $parseResult['categories'] as $categoryInfo ) {
				if( array_key_exists( $categoryInfo['*'], $catLicenseMap ) ) {
					$extensionLicenseItemIdString = $catLicenseMap[$categoryInfo['*']];
				}
			}
		}
		if( $extensionLicenseItemIdString !== null ) {
			$output->writeln( "Creating Licence Statement" );
			$statementCreator = $targetWbFactory->newStatementCreator();
			//TODO make sure it isn't already there????
			$statementCreator->create(
				new PropertyValueSnak(
					new PropertyId( 'P275' ),
					new EntityIdValue( new ItemId( $extensionLicenseItemIdString ) )
				),
				$item->getId()
			);
		}

	}

}
