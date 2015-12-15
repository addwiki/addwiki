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

/**
 * @author Addshore
 */
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

	/**
	 * @var MicrodataExtractor
	 */
	private $microDataExtractor;

	/**
	 * @var Referencer[]
	 */
	private $referencers;

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
			)
			->addOption(
				'type',
				null,
				InputOption::VALUE_REQUIRED,
				'The schema type to target'
			);
	}

	/**
	 * @param string $microdataType eg. "Movie"
	 */
	private function initExecutionServices( $microdataType ) {
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
		$allReferencers = array(
			'Movie' => array(
				new MovieDirectorReferencer( $this->wikibaseFactory ),
				new MovieActorReferencer( $this->wikibaseFactory ),
			),
		);
		$this->referencers = $allReferencers[$microdataType];
		$this->microDataExtractor = new MicrodataExtractor( $microdataType );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN DEVELOPMENT (It's your fault if something goes wrong!)" );

		// Get options
		$type = $input->getOption( 'type' );
		if( $type === null ) {
			throw new RuntimeException( 'You must pass a type' );
		}
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->get( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		// Init the command execution services
		$this->initExecutionServices( $type );

		// Run the query
		$output->writeln( "Running initial query" );
		$itemIds = $this->sparqlQueryRunner->getItemIdsFromQuery(
			$this->sparqlQueryLibrary->getQueryForSchemaType( $type )
		);
		$output->writeln( "Got " . count( $itemIds ) . " items to investigate" );

		// Log in to Wikidata
		$loggedIn =
			$this->wikibaseApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if ( !$loggedIn ) {
			$output->writeln( 'Failed to log in to wikibase wiki' );
			return -1;
		}

		$this->executeForItemIds(
			$output,
			$itemIds
		);

		return 0;
	}

	/**
	 * @param OutputInterface $output
	 * @param ItemId[] $itemIds
	 */
	private function executeForItemIds( OutputInterface $output, array $itemIds ) {
		$itemLookup = $this->wikibaseFactory->newItemLookup();
		$processedItemIdStrings = $this->getProcessedItemIdStrings();
		foreach ( $itemIds as $itemId ) {
			$output->write( $itemId->getSerialization() . ", " );
			if( in_array( $itemId->getSerialization(), $processedItemIdStrings ) ) {
				$output->writeln( "Already processed!" );
				continue;
			}

			try {
				$item = $itemLookup->getItemForId( $itemId );
			}
			catch ( ItemLookupException $e ) {
				continue;
			}

			$output->writeln( "" );

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
						$item
					);

				}

			}
			$this->markIdAsProcessed( $itemId );
		}
	}

	private function executeForPageIdentifier(
		OutputInterface $output,
		MediawikiFactory $sourceMwFactory,
		PageIdentifier $sourcePageIdentifier,
		Item $item
	){
		$guzzleClient = new Client();

		$sourceParser = $sourceMwFactory->newParser();
		//TODO fix assumption of the title being here...?
		$output->write( "Parsing " . $sourcePageIdentifier->getTitle()->getText() . ", " );
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
			$output->writeln( "No external links!" );
			return;
		}

		// Make a bunch of requests
		/** @var FutureResponse[] $futureResponses */
		$futureResponses = array();
		$output->write( "Requesting & getting data" );
		foreach( $externalLinks as $link ) {
			//TODO ignore PDFs
			//TODO make a blacklist of URLS that provide no microformat data?
			$futureResponses[$link] = $guzzleClient->get( $link, array( 'future' => true ) );
		}
		// Get the request responses
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
		$output->write( ', ' );

		// Get structured data from the responses
		$referenceCounter = 0;
		foreach( $linkToHtmlMap as $link => $html ) {
			foreach( $this->microDataExtractor->extract( $html ) as $microData ) {
				foreach( $this->referencers as $referencer ) {
					if( $referencer->canAddReferences( $microData ) ) {
						$addedReferences = $referencer->addReferences( $microData, $item, $link );
						$referenceCounter = $referenceCounter + $addedReferences;
					}
				}
			}
		}

		$output->writeln( "$referenceCounter references added to " . $item->getId()->getSerialization() );
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

	/**
	 * @return string[] ItemId serializations Q12 etc
	 */
	private function getProcessedItemIdStrings() {
		$path = $this->getProcessedListPath();
		if( file_exists( $path ) ) {
			return explode( "\n", file_get_contents( $path ) );
		}
		return array();
	}

	private function markIdAsProcessed( ItemId $itemId ) {
		file_put_contents( $this->getProcessedListPath(), $itemId->getSerialization() . "\n", FILE_APPEND );
	}

	private function getProcessedListPath() {
		return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'addwiki-wikidatareferencer-alreadydone.txt';
	}

}
