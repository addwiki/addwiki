<?php

namespace Mediawiki\Bot\Commands\Wikimedia;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
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

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->get( 'defaults.wiki' );
		$defaultUser = $this->appConfig->get( 'defaults.user' );

		$this
			->setName( 'wm:exttowd' )
			->setDescription( 'Edits the page' )
			->addOption(
				'sourcewiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured wiki to find extensions on (A Wikibase Client)',
				$defaultWiki
			)
			->addOption(
				'targetwiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured wiki to acc data to (A Wikibase Repo)',
				$defaultWiki
			)
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
		die( "This is work in progress code" );
		$sourceWiki = $input->getOption( 'sourcewiki' );
		$targetWiki = $input->getOption( 'targetwiki' );
		$user = $input->getOption( 'user' );

		$userDetails = $this->appConfig->get( 'users.' . $user );
		$sourceWikiDetails = $this->appConfig->get( 'wikis.' . $sourceWiki );
		$targetWikiDetails = $this->appConfig->get( 'wikis.' . $targetWiki );

		if( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		if( $sourceWikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}
		if( $targetWikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$pageIdentifier = null;
		if( $input->getOption( 'title' ) != null ) {
			$sourceTitle = $input->getOption( 'title' );
			$pageIdentifier = new PageIdentifier( new Title( $sourceTitle ) );
		} else {
			throw new RuntimeException( 'No titles was set!' );
		}

		$sourceApi = new MediawikiApi( $sourceWikiDetails['url'] );
		$targetApi = new MediawikiApi( $targetWikiDetails['url'] );
		$loggedIn = $targetApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			$output->writeln( 'Failed to log in to target wiki' );
			return -1;
		}

		$sourceMwFactory = new MediawikiFactory( $sourceApi );
		$sourceParser = $sourceMwFactory->newParser();
		$parseResult = $sourceParser->parsePage( $pageIdentifier );

		//Get the wikibase item if it exists
		$wikibaseItemIdString = null;
		if( array_key_exists( 'properties', $parseResult ) ) {
			foreach( $parseResult['properties'] as $pageProp ) {
				if( $pageProp['name'] == 'wikibase_item' ) {
					$wikibaseItemIdString = $pageProp['*'];
				}
			}
		}

		//$targetMwFactory = new MediawikiFactory( $targetApi );
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
		if( $wikibaseItemIdString === null ) {
			$output->writeln( "Creating a new Item" );
			$newItem = new Item();
			$newItem->setLabel( 'en', $sourceTitle );
			//TODO this siteid should come from somewhere?
			$newItem->getSiteLinkList()->setNewSiteLink( 'mediawikiwiki', $sourceTitle );
			$targetRevSaver = $targetWbFactory->newRevisionSaver();
			$newItem = $targetRevSaver->save( new Revision( new Content( $newItem ) ) );
			$wikibaseItemIdString = $newItem->getId()->serialize();
		}

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
			//TODO make sure it isn't already there?
			$statementCreator->create(
				new PropertyValueSnak(
					new PropertyId( 'P275' ),
					new EntityIdValue( new ItemId( $extensionLicenseItemIdString ) )
				),
				$wikibaseItemIdString
			);
		}

	}

}
