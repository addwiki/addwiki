<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;
use Psr\Http\Message\ResponseInterface;
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
use Wikibase\DataModel\Services\Lookup\ItemLookupException;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @author Addshore
 */
class WikidataReferencerCommand extends Command {

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

	/**
	 * @var WikimediaMediawikiFactoryFactory
	 */
	private $wmFactoryFactory;

	/**
	 * @var MicrodataExtractor
	 */
	private $microDataExtractor;

	/**
	 * @var array 'type' => Referencer[]
	 */
	private $referencerMap = array();

	/**
	 * @var string[]
	 */
	private $instanceMap = array();

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;

		$this->wmFactoryFactory = new WikimediaMediawikiFactoryFactory();
		$this->microDataExtractor = new MicrodataExtractor();
		$this->sparqlQueryRunner = new SparqlQueryRunner( new Client() );

		$stack = HandlerStack::create();
		$stack->push( EffectiveUrlMiddleware::middleware() );
		$this->externalLinkClient = new Client( array( 'handler' => $stack ) );

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

		$this->instanceMap = array(
			'Q11424' => 'Movie',
		);
		$this->referencerMap = array(
			'Movie' => array(
				new PersonReferencer(
					$this->wikibaseFactory,
					array(
						'P57' => 'director',
						'P161' => 'actor',
						'P162' => 'producer',
					)
				)
			),
		);

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
				'instance',
				null,
				InputOption::VALUE_OPTIONAL,
				'Instance of item to target'
			)
			->addOption(
				'item',
				null,
				InputOption::VALUE_OPTIONAL,
				'Item to target'
			);
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

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN DEVELOPMENT (It's your fault if something goes wrong!)" );

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->get( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		$instanceOfString = $input->getOption( 'instance' );
		$item = $input->getOption( 'item' );

		// Get a list of ItemIds
		if( $item !== null ) {
			$itemIds = array( new ItemId( $item ) );
		} elseif( $instanceOfString !== null ) {
			$output->writeln( "Running SPARQL query" );
			//TODO allow the requiring of one or more property ids for statements!
			$itemIds = $this->sparqlQueryRunner->getItemIdsForInstanceOf( new ItemId( $instanceOfString ) );
		} else {
			throw new RuntimeException( 'You must pass an instance id or an item' );
		}
		shuffle( $itemIds );
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

			$output->write( $itemId->getSerialization() . ' ' );

			if( in_array( $itemId->getSerialization(), $processedItemIdStrings ) ) {
				$output->writeln( "Already processed!" );
				continue;
			}

			try {
				$item = $itemLookup->getItemForId( $itemId );
			}
			catch ( ItemLookupException $e ) {
				$output->writeln( "Failed to get item!" );
				continue;
			}

			// Get the item types..
			$types = array();
			foreach( $item->getStatements()->getByPropertyId( new PropertyId( 'P31' ) )->toArray() as $instanceStatement ) {
				$mainSnak = $instanceStatement->getMainSnak();
				if( $mainSnak instanceof PropertyValueSnak ) {
					/** @var EntityIdValue $instanceItemIdValue */
					$instanceItemIdValue = $mainSnak->getDataValue();
					$idSerialization = $instanceItemIdValue->getEntityId()->getSerialization();
					if( array_key_exists( $idSerialization, $this->instanceMap ) ) {
						$types[] = $this->instanceMap[$idSerialization];
					}
				}
			}
			if( empty( $types ) ) {
				$output->writeln( "No matches instance ofs for item!" );
				continue;
			}

			$links = $this->getExternalLinksFromItemWikipediaSitelinks( $item );

			/** @var Request[] $linkRequests */
			$linkRequests = array();
			foreach( $links as $link ) {
				//TODO FIXME temporarily ignore imdb spam?
				if( strstr( $link, 'imdb.' ) === false ) {
					$linkRequests[] = new Request(
						'GET',
						$link,
						array( 'allow_redirects' => array( 'track_redirects' => true ) )
					);
				}
			}

			if ( empty( $linkRequests ) ) {
				$output->writeln( "No external links!" );
				continue;
			}

			// Make a bunch of requests
			$output->write( "Loading " . count( $linkRequests ) . " links" );
			// TODO we want to somehow output the progress of this pool batch...
			$linkResponses = Pool::batch( $this->externalLinkClient, $linkRequests );

			$linkToHtmlMap = array();
			foreach( $linkResponses as $response ) {
				if( $response instanceof ResponseInterface ) {
					$effectiveUrl = $response->getHeaderLine( 'X-GUZZLE-EFFECTIVE-URL' );
					$linkToHtmlMap[$effectiveUrl] = $response->getBody();
					$output->write( '.' );
				} else {
					$output->write( 'e' );
				}
			}
			$output->write( ' ' );

			// Get structured data from the responses
			$output->write( "Adding refs" );
			foreach( $linkToHtmlMap as $link => $html ) {
				foreach( $this->microDataExtractor->extract( $html ) as $microData ) {
					foreach( $types as $type ) {
						if( $microData->hasType( $type ) && array_key_exists( $type, $this->referencerMap ) )
							foreach( $this->referencerMap[$type] as $referencer ) {
								/** @var Referencer $referencer */
								$addedReferences = $referencer->addReferences( $microData, $item, $link );
								$output->write( str_repeat( '.', $addedReferences ) );
							}
					}
				}
			}

			$output->writeln('');
			$this->markIdAsProcessed( $itemId );
		}
	}

	/**
	 * Parses wikipedia sitelinks for external links
	 * TODO also get links currently used as references!
	 *
	 * @param Item $item
	 *
	 * @return string[]
	 */
	private function getExternalLinksFromItemWikipediaSitelinks( Item $item ) {
		/** @var PromiseInterface[] $parsePromises */
		$parsePromises = array();
		foreach ( $item->getSiteLinkList()->getIterator() as $siteLink ) {
			$siteId = $siteLink->getSiteId();
			//Note: only load Wikipedias
			if ( substr( $siteId, -4 ) == 'wiki' ) {
				$pageName = $item->getSiteLinkList()->getBySiteId( $siteId )->getPageName();
				$sourceMwFactory = $this->wmFactoryFactory->getFactory( $siteId );
				$sourceParser = $sourceMwFactory->newParser();
				$pageIdentifier = new PageIdentifier( new Title( $pageName ) );
				$parsePromises[$siteId] = $sourceParser->parsePageAsync( $pageIdentifier );
			}
		}
		$links = array();
		foreach ( $parsePromises as $siteId => $promise ) {
			try {
				$parseResult = $promise->wait();
				if ( array_key_exists( 'externallinks', $parseResult ) ) {
					foreach ( $parseResult['externallinks'] as $externalLink ) {
						//TODO FIXME temporarily ignore imdb spam?
						if ( strstr( $externalLink, 'imdb.' ) === false ) {
							$links[] = $this->normalizeWikipediaExternalLink( $externalLink );
						}
					}
				}
			}
			catch ( Exception $e ) {
				// Ignore failed requests
			}
		}

		return array_unique( $links );
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
