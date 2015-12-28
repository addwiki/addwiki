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
		$stack = HandlerStack::create();
		$stack->push( EffectiveUrlMiddleware::middleware() );
		$defaultGuzzleConf = array(
			'headers' => array( 'User-Agent' => 'addwiki - Wikidata Referencer' ),
			'handler' => $stack,
		);
		$guzzleClient = new Client( $defaultGuzzleConf );

		$this->appConfig = $appConfig;

		$this->wmFactoryFactory = new WikimediaMediawikiFactoryFactory();
		$this->microDataExtractor = new MicrodataExtractor();
		$this->sparqlQueryRunner = new SparqlQueryRunner( $guzzleClient );
		$this->externalLinkClient = $guzzleClient;

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
			'Q5' => 'Person',
			'Q11424' => 'Movie',
		);
		$this->referencerMap = array(
			'Person' => array(
				new ThingReferencer(
					$this->wikibaseFactory,
					array(
						'P7' => 'sibling',//brother
						'P9' => 'sibling',//sister
						'P19' => 'birthPlace',
						'P20' => 'deathPlace',
						'P21' => 'gender',
						'P22' => 'parent',//father
						'P25' => 'parent',//mother
						'P26' => 'spouse',
						'P27' => 'nationality',
						'P734' => 'familyName',
						'P735' => 'givenName',
					)
				),
				new DateReferencer(
					$this->wikibaseFactory,
					array(
						'P569' => 'birthDate',
						'P570' => 'deathDate',
					)
				)
			),
			'Movie' => array(
				new ThingReferencer(
					$this->wikibaseFactory,
					array(
						// Person
						'P57' => 'director',
						'P161' => 'actor',
						'P162' => 'producer',
						'P1040' => 'editor',
						'P58' => 'author',
						// Organization
						'P272' => array( 'creator', 'productionCompany' ),
					)
				),
				new MultiTextReferencer(
					$this->wikibaseFactory,
					array(
						'P136' => 'genre',
					),
					array(
						'P136' => array(
							'Q188473' => '/action( ?film)?/i',
							'Q319221' => '/adventure( ?film)?/i',
							'Q471839' => '/(Science Fiction|Sci-Fi)( ?film)?/i',
							'Q157394' => '/fantasy( ?film)?/i',
						),
					)
				),
				new DateReferencer(
					$this->wikibaseFactory,
					array(
						'P577' => 'datePublished',
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
				'sparql',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'SPARQL query part'
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
	private function normalizeExternalLink( $link ) {
		if ( strpos( $link, '//' ) === 0 ) {
			$link = 'http' . $link;
		}
		if( strpos( $link, '#' ) !== false ) {
			$link = strstr( $link, '#', true );
		}
		$link = trim( $link, '/' );

		// Normalize some domain specific stuff
		if( strstr( $link, '.imdb.' ) ) {
			$link = preg_replace( '#\/\/[^.]+\.imdb\.[^/]+\/#i', '//www.imdb.com/', $link );
		}

		return $link;
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN DEVELOPMENT (It's your fault if something goes wrong!)" );
		$output->writeln( "Temp file: " . $this->getProcessedListPath() );

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->get( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		$sparqlQueryParts = $input->getOption( 'sparql' );
		$item = $input->getOption( 'item' );
		$force = false;

		// Get a list of ItemIds
		if( $item !== null ) {
			$itemIds = array( new ItemId( $item ) );
			// Force if explicitly passed an ItemId
			$force = true;
		} elseif( !empty( $sparqlQueryParts ) ) {
			$output->writeln( "Running SPARQL query with " . count( $sparqlQueryParts ) . ' parts' );
			$itemIds = $this->sparqlQueryRunner->getItemIdsForSimpleQueryParts( $sparqlQueryParts );
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
			$itemIds,
			$force
		);

		return 0;
	}

	/**
	 * @param OutputInterface $output
	 * @param ItemId[] $itemIds
	 * @param bool $force
	 */
	private function executeForItemIds( OutputInterface $output, array $itemIds, $force ) {
		$itemLookup = $this->wikibaseFactory->newItemLookup();
		$processedItemIdStrings = $this->getProcessedItemIdStrings();
		foreach ( $itemIds as $itemId ) {

			$output->write( $itemId->getSerialization() . ' ' );

			if( !$force && in_array( $itemId->getSerialization(), $processedItemIdStrings ) ) {
				$output->writeln( "Already processed!" );
				continue;
			}

			try {
				$item = $itemLookup->getItemForId( $itemId );
				$output->write( 'I' );
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
				$output->writeln( " No matches instance ofs for item!" );
				continue;
			}

			$links = $this->getExternalLinksFromItemWikipediaSitelinks( $item, $output );
			shuffle( $links );

			/** @var Request[] $linkRequests */
			$linkRequests = array();
			foreach( $links as $link ) {
				$linkRequests[] = new Request(
					'GET',
					$link,
					array( 'allow_redirects' => array( 'track_redirects' => true ) )
				);
			}

			if ( empty( $linkRequests ) ) {
				$output->writeln( " No external links!" );
				continue;
			} else {
				$output->write( ' ' . count( $linkRequests ) . ' external links: ' );
			}

			// Make a bunch of requests and act on the responses
			foreach( array_chunk( $linkRequests, 100 ) as $linkRequestChunk ) {
				$linkResponses = Pool::batch(
					$this->externalLinkClient,
					$linkRequestChunk,
					array(
						'fulfilled' => function () use ( $output ) { $output->write( 'l' ); },
						'rejected' => function () use ( $output ) { $output->write( 'e' ); },
					)
				);
				$linkToHtmlMap = array();
				foreach( $linkResponses as $response ) {
					if( $response instanceof ResponseInterface ) {
						$effectiveUrl = $response->getHeaderLine( 'X-GUZZLE-EFFECTIVE-URL' );
						$linkToHtmlMap[$effectiveUrl] = $response->getBody();
					}
				}

				// Get structured data from the responses
				foreach( $linkToHtmlMap as $link => $html ) {
					foreach( $this->microDataExtractor->extract( $html ) as $microData ) {
						foreach( $types as $type ) {
							if( $microData->hasType( $type ) && array_key_exists( $type, $this->referencerMap ) )
								foreach( $this->referencerMap[$type] as $referencer ) {
									/** @var Referencer $referencer */
									$addedReferences = $referencer->addReferences( $microData, $item, $link );
									$output->write( str_repeat( 'R', $addedReferences ) );
								}
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
	 * @param OutputInterface $output
	 *
	 * @return \string[]
	 */
	private function getExternalLinksFromItemWikipediaSitelinks( Item $item, OutputInterface $output ) {
		$output->write( ' ' . $item->getSiteLinkList()->count() . ' site links: ' );

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
				$output->write( 'p' );
				if ( array_key_exists( 'externallinks', $parseResult ) ) {
					foreach ( $parseResult['externallinks'] as $externalLink ) {
						// Ignore archive.org links
						if ( strstr( $externalLink, 'archive.org' ) === false ) {
							$links[] = $this->normalizeExternalLink( $externalLink );
						}
					}
				}
			}
			catch ( Exception $e ) {
				$output->write( 'e' );
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
