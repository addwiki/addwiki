<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer;

use Addwiki\Mediawiki\Api\Client\ApiUser;
use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Guzzle\ClientFactory;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Title;
use Addwiki\Wikibase\Api\WikibaseFactory;
use Addwiki\Wikimedia\Api\WikimediaFactory;
use Addwiki\Wikimedia\Commands\SparqlQueryRunner;
use Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData\MicroDataExtractor;
use Addwiki\Wikimedia\Commands\WikidataReferencer\Referencers\Referencer;
use ArrayAccess;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\ItemLookupException;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class WikidataReferencerCommand extends Command {

	private ArrayAccess $appConfig;

	private ?SparqlQueryRunner $sparqlQueryRunner = null;

	private ?WikibaseFactory $wikibaseFactory = null;

	private ?MediawikiApi $wikibaseApi = null;

	private ?WikimediaMediawikiFactoryFactory $wmFactoryFactory = null;

	private ?MicroDataExtractor $microDataExtractor = null;

	/**
	 * @var Referencer[] 'type' => Referencer[]
	 */
	private array $referencerMap = [];

	/**
	 * @var string[]
	 */
	private array $instanceMap = [];

	private ?Client $externalLinkClient = null;

	private ?string $tmpDir = null;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	public function initServices(): void {
		$clientFactory = new ClientFactory(
			[
				'middleware' => [ EffectiveUrlMiddleware::middleware() ],
				'user-agent' => 'Addwiki - Wikidata Referencer',
			]
		);
		$guzzleClient = $clientFactory->getClient();

		$this->wmFactoryFactory = new WikimediaMediawikiFactoryFactory( $clientFactory );
		$this->microDataExtractor = new MicroDataExtractor();
		$this->sparqlQueryRunner = new SparqlQueryRunner( $guzzleClient );
		$this->externalLinkClient = $guzzleClient;

		$this->wikibaseApi = new MediawikiApi( 'https://www.wikidata.org/w/api.php', null, $guzzleClient );
		$this->wikibaseFactory = ( new WikimediaFactory() )->newWikidataWikibaseFactory();

		$mapper = new WikidataToSchemaMapper();
		$this->instanceMap = $mapper->getInstanceMap();
		$this->referencerMap = $mapper->getReferencerMap(
			$this->wikibaseFactory,
			$this->sparqlQueryRunner
		);
	}

	protected function configure() {
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

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
			)
			->addOption(
				'tmpDir',
				null,
				InputOption::VALUE_OPTIONAL,
				'Temporary directory to store a processed ID list in'
			);
	}

	private function normalizeExternalLink( string $link ): ?string {
		if ( strpos( $link, '//' ) === 0 ) {
			$link = 'http' . $link;
		}
		if ( strpos( $link, '#' ) !== false ) {
			$link = strstr( $link, '#', true );
		}
		$link = trim( $link, '/' );

		// Normalize some domain specific stuff
		if ( strstr( $link, '.imdb.' ) ) {
			$link = preg_replace( '#\/\/[^.]+\.imdb\.[^/]+\/#i', '//www.imdb.com/', $link );
		}

		return $link;
	}

	/**
	 * @return int
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$this->initServices();

		$this->tmpDir = is_string( $input->getOption( 'tmpDir' ) ) ? $input->getOption( 'tmpDir' ) : sys_get_temp_dir();
		if ( !is_writable( $this->tmpDir ) ) {
			throw new RuntimeException( 'Temp dir: ' . $this->tmpDir . ' is not writable' );
		}

		/** @var FormatterHelper $formatter */
		$formatter = $this->getHelper( 'formatter' );
		$output->writeln( $formatter->formatBlock(
			[
				'Wikidata Referencer',
				'This script is in development, If something goes wrong while you use it it is your fault!',
				'Temp file: ' . $this->getProcessedListPath(),
			],
			'info'
		) );

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->offsetGet( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		$sparqlQueryParts = $input->getOption( 'sparql' );
		$item = $input->getOption( 'item' );
		$force = false;

		// Get a list of ItemIds
		if ( $item !== null ) {
			$output->writeln( $formatter->formatSection( 'Init', 'Using item passed in item parameter' ) );
			$itemIds = [ new ItemId( $item ) ];
			// Force if explicitly passed an ItemId
			$force = true;
		} elseif ( !empty( $sparqlQueryParts ) ) {
			$output->writeln( $formatter->formatSection( 'Init', 'Using items from SPARQL QUERY (running)' ) );
			$itemIds = $this->sparqlQueryRunner->getItemIdsForSimpleQueryParts( $sparqlQueryParts );
		} else {
			throw new RuntimeException( 'You must pass an instance id or an item' );
		}
		shuffle( $itemIds );
		$output->writeln( $formatter->formatSection( 'Init', 'Got ' . count( $itemIds ) . ' items to investigate' ) );

		// Log in to Wikidata
		$loggedIn =
			$this->wikibaseApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if ( !$loggedIn ) {
			throw new RuntimeException( 'Failed to log in to wikibase wiki' );
		}

		$this->executeForItemIds(
			$output,
			$itemIds,
			$force
		);

		return 0;
	}

	/**
	 * @param ItemId[] $itemIds
	 */
	private function executeForItemIds( OutputInterface $output, array $itemIds, bool $force ): void {
		$itemLookup = $this->wikibaseFactory->newItemLookup();
		$processedItemIdStrings = $this->getProcessedItemIdStrings();
		$loopCounter = 0;
		/** @var FormatterHelper $formatter */
		$formatter = $this->getHelper( 'formatter' );
		foreach ( $itemIds as $itemId ) {
			++$loopCounter;
			$itemIdString = $itemId->getSerialization();

			$output->writeln( '----------------------------------------------------' );

			if ( $loopCounter % 10 != 0 ) {
				$processedItemIdStrings = $this->getProcessedItemIdStrings();
			}
			if ( !$force && in_array( $itemId->getSerialization(), $processedItemIdStrings ) ) {
				$output->writeln( $formatter->formatSection( $itemIdString, 'Already processed' ) );
				continue;
			}

			try {
				$output->writeln( $formatter->formatSection( $itemIdString, 'Loading Item' ) );
				$item = $itemLookup->getItemForId( $itemId );
			}
			catch ( ItemLookupException $itemLookupException ) {
				$output->writeln( $formatter->formatSection( $itemIdString, 'Failed to load item (exception)', 'error' ) );
				continue;
			}

			if ( !$item instanceof Item ) {
				$output->writeln( $formatter->formatSection( $itemIdString, 'Failed to load item (null)', 'error' ) );
				continue;
			}

			// Get the item types..
			$types = [];
			foreach ( $item->getStatements()->getByPropertyId( new PropertyId( 'P31' ) )->toArray() as $instanceStatement ) {
				$mainSnak = $instanceStatement->getMainSnak();
				if ( $mainSnak instanceof PropertyValueSnak ) {
					/** @var EntityIdValue $instanceItemIdValue */
					$instanceItemIdValue = $mainSnak->getDataValue();
					$idSerialization = $instanceItemIdValue->getEntityId()->getSerialization();
					if ( array_key_exists( $idSerialization, $this->instanceMap ) ) {
						$types[] = $this->instanceMap[$idSerialization];
					}
				}
			}
			if ( empty( $types ) ) {
				$output->writeln( $formatter->formatSection( $itemIdString, 'Didn\t find any useful instance of statements', 'comment' ) );
				continue;
			}

			// Note: only load Wikipedias
			$siteLinkList = DataModelUtils::getSitelinksWiteSiteIdSuffix(
				$item->getSiteLinkList(),
				'wiki'
			);

			$output->writeln( $formatter->formatSection( $itemIdString, $siteLinkList->count() . ' Wikipedia pages to request' ) );
			$parseProgressBar = new ProgressBar( $output, $siteLinkList->count() );
			$parseProgressBar->display();
			/** @var PromiseInterface[] $parsePromises */
			$parsePromises = [];
			/** Suppressions can be removed once https://github.com/wmde/WikibaseDataModel/pull/838 is released */
			/** @psalm-suppress UndefinedDocblockClass */
			/** @psalm-suppress UndefinedClass */
			foreach ( $siteLinkList->getIterator() as $siteLink ) {
				$siteId = $siteLink->getSiteId();
				$pageName = $item->getSiteLinkList()->getBySiteId( $siteId )->getPageName();
				$sourceMwFactory = $this->wmFactoryFactory->getFactory( $siteId );
				$sourceParser = $sourceMwFactory->newParser();
				$pageIdentifier = new PageIdentifier( new Title( $pageName ) );
				$parsePromises[$siteId] = $sourceParser->parsePageAsync( $pageIdentifier );
				$parseProgressBar->advance();
			}
			$links = [];
			foreach ( $parsePromises as $promise ) {
				try {
					$parseResult = $promise->wait();
					if ( array_key_exists( 'externallinks', $parseResult ) ) {
						foreach ( $parseResult['externallinks'] as $externalLink ) {
							// Ignore archive.org links
							if ( strstr( $externalLink, 'archive.org' ) === false ) {
								$links[] = $this->normalizeExternalLink( $externalLink );
							}
						}
					}
				}
				catch ( Exception $exception ) {
					$parseProgressBar->clear();
					$output->writeln( $formatter->formatSection( $itemIdString, $exception->getMessage(), 'error' ) );
					$parseProgressBar->display();
					// Ignore failed requests
				}
			}
			$parseProgressBar->finish();
			$output->writeln( '' );

			$links = array_unique( $links );
			shuffle( $links );

			/** @var Request[] $linkRequests */
			$linkRequests = [];
			foreach ( $links as $link ) {
				$linkRequests[] = new Request(
					'GET',
					$link,
					[
						'allow_redirects' => [ 'track_redirects' => true ],
						'connect_timeout' => 3.14,
						'timeout' => 10,
					]
				);
			}

			$output->writeln( $formatter->formatSection( $itemIdString, count( $linkRequests ) . ' External links to (download, action)' ) );
			if ( empty( $linkRequests ) ) {
				continue;
			}

			// Make a bunch of requests and act on the responses
			$referencesAddedToItem = 0;
			$externalLinkProgressBar = new ProgressBar( $output, count( $linkRequests ) * 2 );
			$externalLinkProgressBar->display();

			$pool = new Pool(
				$this->externalLinkClient,
				$linkRequests,
				[
					'fulfilled' => function ( $response )
					use ( $externalLinkProgressBar, $item, $types, $referencesAddedToItem, $output ): void {
						$externalLinkProgressBar->advance(); // 1st advance point

						if ( $response instanceof ResponseInterface ) {
							$link = $response->getHeaderLine( 'X-GUZZLE-EFFECTIVE-URL' );
							$html = $response->getBody();
							$referencesAddedFromLink = 0;

							foreach ( $this->microDataExtractor->extract( $html ) as $microData ) {
								foreach ( $types as $type ) {
									if ( $microData->hasType( $type ) && array_key_exists( $type, $this->referencerMap ) ) {
										foreach ( $this->referencerMap[$type] as $referencer ) {
											/** @var Referencer $referencer */
											$addedReferences = $referencer->addReferences( $microData, $item, $link );
											$referencesAddedToItem += $addedReferences;
											$referencesAddedFromLink += $addedReferences;
										}
									}
								}
							}
							if ( $referencesAddedFromLink > 0 ) {
								$externalLinkProgressBar->clear();
								$output->write( "\x0D" );
								$output->writeln( $referencesAddedFromLink . ' reference(s) added from ' . urldecode( $link ) );
								$externalLinkProgressBar->display();
							}

						}
						$externalLinkProgressBar->advance(); // 2nd advance point
					},

					'rejected' => function () use ( $externalLinkProgressBar ): void {
						// TODO add this to some kind of verbose log?
						$externalLinkProgressBar->advance(); // 1st advance point
					},
				]
			);

			$pool->promise()->wait();
			$externalLinkProgressBar->finish();
			$output->writeln( '' );
			$output->writeln( $formatter->formatSection( $itemIdString, $referencesAddedToItem . ' References added' ) );

			$this->markIdAsProcessed( $itemId );
		}
	}

	/**
	 * @return string[] ItemId serializations Q12 etc
	 */
	private function getProcessedItemIdStrings(): array {
		$path = $this->getProcessedListPath();
		if ( file_exists( $path ) ) {
			return explode( PHP_EOL, file_get_contents( $path ) );
		}
		return [];
	}

	private function markIdAsProcessed( ItemId $itemId ): void {
		file_put_contents( $this->getProcessedListPath(), $itemId->getSerialization() . PHP_EOL, FILE_APPEND );
	}

	private function getProcessedListPath(): string {
		return $this->tmpDir . DIRECTORY_SEPARATOR . 'addwiki-wikidatareferencer-alreadydone.txt';
	}

}
