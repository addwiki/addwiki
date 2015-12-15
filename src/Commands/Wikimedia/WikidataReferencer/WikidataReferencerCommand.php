<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use DataValues\StringValue;
use DataValues\TimeValue;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use linclark\MicrodataPHP\MicrodataPhp;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Services\Lookup\ItemLookupException;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;

class WikidataReferencerCommand extends Command {

	private $appConfig;

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

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN TESTING, if you use it it is your fault if anything goes wrong" );

		// Get options
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->get( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		// Create a pile of services
		$guzzleClient = new Client();
		$sparqlQueryLibrary = new SparqlQueryLibrary();
		$sparqlQueryRunner = new SparqlQueryRunner( $guzzleClient );
		$wikibaseApi = new MediawikiApi( "https://www.wikidata.org/w/api.php" );
		$wikibaseFactory = new WikibaseFactory(
			$wikibaseApi,
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
		$itemLookup = $wikibaseFactory->newItemLookup();

		// Run the query
		$output->writeln( "Running SPARQL query" );
		$itemIdsOfInterest = $sparqlQueryRunner->getItemIdsFromQuery(
			$sparqlQueryLibrary->getQueryForSchemaType( "Movie" )
		);
		$output->writeln( "Got " . count( $itemIdsOfInterest ) . " items of interest" );

		// Log in to Wikidata
		$output->writeln( "Logging in" );
		$loggedIn =
			$wikibaseApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if ( !$loggedIn ) {
			$output->writeln( 'Failed to log in to wikibase wiki' );
			return -1;
		}

		foreach( $itemIdsOfInterest as $itemId ) {
			try{
				$item = $itemLookup->getItemForId( $itemId );
			} catch ( ItemLookupException $e ) {
				continue;
			}

			$allowedWikiCodes = array( 'enwiki', 'dewiki', 'svwiki', 'nlwiki', 'frwiki', 'ruwiki', 'itwiki', 'eswiki', 'plwiki', 'ptwiki' );
			foreach( $allowedWikiCodes as $siteId ) {
				if( $item->getSiteLinkList()->hasLinkWithSiteId( $siteId ) ) {
					$pageName = $item->getSiteLinkList()->getBySiteId( $siteId )->getPageName();
					$sourceWikiDetails = $this->appConfig->get( 'wikis.' . $siteId );
					if ( $sourceWikiDetails === null ) {
						throw new RuntimeException( 'Source wiki not found in config' );
					}
					$sourceApi = new MediawikiApi( $sourceWikiDetails['url'] );
					$sourceMwFactory = new MediawikiFactory( $sourceApi );

					$pageIdentifier = new PageIdentifier( new Title( $pageName ) );
					$this->executeForPageIdentifier(
						$output,
						$sourceMwFactory,
						$wikibaseFactory,
						$guzzleClient,
						$pageIdentifier,
						$item,
						$siteId
					);

				}

			}
		}

		return 0;
	}

	private function executeForPageIdentifier(
		OutputInterface $output,
		MediawikiFactory $sourceMwFactory,
		WikibaseFactory $wikibaseFactory,
		Client $guzzleClient,
		PageIdentifier $sourcePageIdentifier,
		Item $item,
		$sourceWikiCode
	){

		$sourceParser = $sourceMwFactory->newParser();
		//TODO fix assumption of the title being here...?
		$output->writeln( "-- Parsing page " . $sourcePageIdentifier->getTitle()->getText() . " from $sourceWikiCode --" );
		$parseResult = $sourceParser->parsePage( $sourcePageIdentifier );

		$externalLinks = array();
		if ( array_key_exists( 'externallinks', $parseResult ) ) {
			foreach( $parseResult['externallinks'] as $externalLink ) {
				//TODO FIXME temporarily ignore imdb spam?
				if( strstr( $externalLink, '.imdb.' ) === false ) {
					$externalLinks[] = $this->normalizeWikipediaExternalLink( $externalLink );
				}
			}
		}

		if ( empty( $externalLinks ) ) {
			$output->writeln( "Could not find any external links for the given page" );
			return -1;
		}

		$movieMicrodatas = array();

		//Make a bunch of requests
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

		// Get structured data from the responses
		$output->write( 'Getting Movie microdata' );
		foreach ( $futureResponses as $link => $futureResponse ) {
			try {
				$md = new MicrodataPhp( array( 'html' => $futureResponse->getBody() ) );
			}
			catch ( Exception $e ) {
				continue;
			}

			$data = $md->obj();
			$addedForThisLink = 0;
			foreach ( $data->items as $microdata ) {
				//TODO also match https or protocol relative?
				if ( in_array( 'http://schema.org/Movie', $microdata->type ) ) {
					$output->write( '.' );
					$microdata->url = $link;
					$movieMicrodatas[] = $microdata;
					$addedForThisLink++;
				}
			}
		}
		$output->writeln( '' );

		$output->write( 'Adding references' );
		foreach ( $movieMicrodatas as $dataObject ) {
			$sourceUrl = $dataObject->url;
			/** @var array $dataProperty */
			foreach ( $dataObject->properties as $dataPropertyName => $dataProperties ) {
				if ( $dataPropertyName == 'director' ) {
					foreach ( $dataProperties as $innerDataObject ) {
						if ( is_object( $innerDataObject ) && array_key_exists( 'properties', $innerDataObject ) ) {
							if ( array_key_exists( 'name', $innerDataObject->properties ) ) {
								$directorName = $innerDataObject->properties['name'][0];
								//TODO check this is right and if so add a ref?

								//NOTE: P57 is director
								$directorStatements = $item->getStatements()->getByPropertyId( new PropertyId( 'P57' ) );
								/** @var Statement $directorStatement */
								foreach ( $directorStatements as $directorStatement ) {
									/** @var PropertyValueSnak $mainSnak */
									$mainSnak = $directorStatement->getMainSnak();
									if ( $mainSnak->getType() == 'value' ) {
										/** @var EntityIdValue $directorItemId */
										$directorItemId = $mainSnak->getDataValue();
										$directorItemRevision = $wikibaseFactory->newRevisionGetter()->getFromId( $directorItemId->getEntityId() );
										/** @var Item|FingerprintProvider $directorItem */
										$directorItem = $directorItemRevision->getContent()->getData();
										$englishTerms = array_map( 'strtolower', $this->getTermsAsStrings( $directorItem->getFingerprint() ) );
										if( in_array( strtolower( $directorName ), $englishTerms ) ) {

											$currentReferences = $directorStatement->getReferences();
											$alreadyHasRefForThisUrl = false;
											/** @var Reference $currentReference */
											foreach( $currentReferences as $currentReference ) {
												//TODO fix the value snak assumption below
												/** @var PropertyValueSnak $currentReferenceSnak */
												foreach( $currentReference->getSnaks() as $currentReferenceSnak ) {
													//Note: P854 is reference URL
													if( $currentReferenceSnak->getPropertyId()->getSerialization() == 'P854' ) {
														/** @var StringValue $currentReferenceValue */
														$currentReferenceValue = $currentReferenceSnak->getDataValue();
														$currentReferenceUrl = $currentReferenceValue->getValue();
														if( $this->urlsAreSame( $currentReferenceUrl, $sourceUrl ) ) {
															$alreadyHasRefForThisUrl = true;
														}
													}
												}
											}

											//If no ref already then add a ref
											if( $alreadyHasRefForThisUrl == false ) {
												$output->write( '.' );
												$newRef = new Reference( array(
													// Source URL
													new PropertyValueSnak( new PropertyId( 'P854' ), new StringValue( $sourceUrl ) ),
													// Date retrieved
													new PropertyValueSnak( new PropertyId( 'P813' ), $this->getWikidataNowTimeValue() )
													// TODO date published?
												) );
												$editInfo = new EditInfo( "From $sourceWikiCode with love" );
												$wikibaseFactory->newReferenceSetter()->set( $newRef, $directorStatement, null, $editInfo );
												//NOTE: keep our in memory item copy up to date
												$directorStatement->addNewReference( $newRef->getSnaks() );
											}
										}
									}
								}

							}
						}
					}
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

	private function getTermsAsStrings( Fingerprint $fingerprint ) {
		$strings = array();
		$englishLangs = array( 'en', 'en-gb' );
		foreach( $englishLangs as $lang ) {
			try{
				$strings[] = $fingerprint->getLabel( $lang )->getText();
			} catch ( Exception $e ) {
				// Ignore!
			}
			try{
				$strings = array_merge( $strings, $fingerprint->getAliasGroup( $lang )->getAliases() );
			} catch ( Exception $e ) {
				// Ignore!
			}
		}
		return $strings;

	}

	private function getWikidataNowTimeValue() {
		return new TimeValue(
			"+" . date( 'Y-m-d' ) . "T00:00:00Z",
			0,//TODO dont assume UTC
			0,
			0,
			TimeValue::PRECISION_DAY,
			TimeValue::CALENDAR_JULIAN
		);
	}

	/**
	 * @param string $a
	 * @param string $b
	 *
	 * @return bool
	 */
	private function urlsAreSame( $a, $b ) {
		$regex = '#^https?://#';
		$a = preg_replace($regex, '', $a);
		$b = preg_replace($regex, '', $b);
		$a = trim( $a, "/" );
		$b = trim( $b, "/" );
		return $a == $b;
	}

}
