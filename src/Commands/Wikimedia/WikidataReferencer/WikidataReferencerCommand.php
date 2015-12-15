<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\ItemLookupException;

class WikidataReferencerCommand extends Command {

	private $appConfig;

	/**
	 * @var SparqlQueryLibrary
	 */
	private $sparqlQueryLibrary;

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

	/**
	 * @var WikimediaMediawikiFactoryFactory
	 */
	private $wmFactoryFactory;

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultUser = $this->appConfig->get( 'defaults.user' );

		$this
			->setName( 'wm:wd:ref' )
			->setDescription( 'Adds references to Wikidata items' )
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured user to use',
				$defaultUser
			);
	}

	private function initExecutionServices() {
		$this->sparqlQueryLibrary = new SparqlQueryLibrary();
		$this->sparqlQueryRunner = new SparqlQueryRunner( new Client() );
		$this->wikibaseApi = new MediawikiApi( "https://www.wikidata.org/w/api.php" );
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
		$this->wmFactoryFactory = new WikimediaMediawikiFactoryFactory();
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN DEVELOPMENT (It's your fault if something does wrong!)" );

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->get( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		// Init the command execution services
		$this->initExecutionServices();

		// Run the query
		$itemIdsOfInterest = $this->sparqlQueryRunner->getItemIdsFromQuery(
			$this->sparqlQueryLibrary->getQueryForSchemaType( "Movie" )
		);
		$output->writeln( "Got " . count( $itemIdsOfInterest ) . " items to investigate" );

		// Log in to Wikidata
		$loggedIn =
			$this->wikibaseApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if ( !$loggedIn ) {
			$output->writeln( 'Failed to log in to wikibase wiki' );
			return -1;
		}

		$this->executeForItemIds(
			$output,
			$itemIdsOfInterest
		);

		return 0;
	}

	/**
	 * @param OutputInterface $output
	 * @param ItemId[] $itemIds
	 */
	private function executeForItemIds( OutputInterface $output, array $itemIds ) {
		$itemLookup = $this->wikibaseFactory->newItemLookup();
		foreach ( $itemIds as $itemId ) {
			try {
				$item = $itemLookup->getItemForId( $itemId );
			}
			catch ( ItemLookupException $e ) {
				continue;
			}

			$allowedWikiCodes =
				array(
					'enwiki',
					'dewiki',
					'svwiki',
					'nlwiki',
					'frwiki',
					'ruwiki',
					'itwiki',
					'eswiki',
					'plwiki',
					'ptwiki',
				);
			foreach ( $allowedWikiCodes as $siteId ) {
				if ( $item->getSiteLinkList()->hasLinkWithSiteId( $siteId ) ) {
					$pageName = $item->getSiteLinkList()->getBySiteId( $siteId )->getPageName();
					$sourceMwFactory = $this->wmFactoryFactory->getFactory( $siteId );

					$pageIdentifier = new PageIdentifier( new Title( $pageName ) );
					$this->executeForPageIdentifier(
						$output,
						$sourceMwFactory,
						$pageIdentifier,
						$item,
						$siteId
					);

				}

			}
		}
	}

	private function executeForPageIdentifier(
		OutputInterface $output,
		MediawikiFactory $sourceMwFactory,
		PageIdentifier $sourcePageIdentifier,
		Item $item,
		$sourceWikiCode
	){
		$guzzleClient = new Client();

		$sourceParser = $sourceMwFactory->newParser();
		//TODO fix assumption of the title being here...?
		$output->writeln( "-- Parsing page " . $sourcePageIdentifier->getTitle()->getText() . " from $sourceWikiCode --" );
		$parseResult = $sourceParser->parsePage( $sourcePageIdentifier );

		$externalLinks = array();
		if ( array_key_exists( 'externallinks', $parseResult ) ) {
			foreach( $parseResult['externallinks'] as $externalLink ) {
				//TODO FIXME temporarily ignore imdb spam?
				if( strstr( $externalLink, 'imdb.' ) === false ) {
					$externalLinks[] = $this->normalizeWikipediaExternalLink( $externalLink );
				}
			}
		}

		if ( empty( $externalLinks ) ) {
			$output->writeln( "Could not find any external links for the given page" );
			return -1;
		}

		// Make a bunch of requests
		/** @var FutureResponse[] $futureResponses */
		$futureResponses = array();
		$output->write( "Making requests" );
		foreach( $externalLinks as $link ) {
			//TODO ignore PDFs
			//TODO make a blacklist of URLS that provide no microformat data?
			$futureResponses[$link] = $guzzleClient->get( $link, array( 'future' => true ) );
			$output->write( '.' );
		}
		$output->writeln( '' );

		// Get the request responses
		$output->write( "Getting all HTML responses" );
		$linkToHtmlMap = array();
		foreach ( $futureResponses as $link => $futureResponse ) {
			try {
				$output->write( '.' );
				$linkToHtmlMap[$link] = $futureResponse->getBody();
			}
			catch ( Exception $e ) {
				continue;
			}
		}
		$output->writeln( '' );

		// Get structured data from the responses
		$output->writeln( 'Getting microdata from HTML' );
		$microDataMap = array();
		$microDataExtractor = new MicrodataExtractor();
		foreach( $linkToHtmlMap as $link => $html ) {
			$microDataMap[$link] = $microDataExtractor->extract( $html, 'Movie' );
		}

		// Try to add references for the microdata stuff
		$output->write( 'Attempting to add references' );
		/** @var MicroData[] $microDataObjects */
		foreach ( $microDataMap as $sourceUrl => $microDataObjects ) {
			foreach( $microDataObjects as $microData ) {
				$referencer = new MovieDirectorReferencer( $this->wikibaseFactory );
				if( $referencer->canAddReferences( $microData ) ) {
					$referencer->addReferences( $microData, $item, $sourceUrl, $sourceWikiCode );
				}
			}
		}
		$output->writeln( '' );

	}

	/**
	 * @param string $link
	 *
	 * @return string
	 */
	private function normalizeWikipediaExternalLink( $link ) {
		if ( strpos( $link, '//' ) === 0 ) {
			$link = 'http' . $link;
		}
		if( strpos( $link, '#' ) !== false ) {
			$link = strstr( $link, '#', true );
		}
		$link = trim( $link, '/' );
		return $link;
	}

}
