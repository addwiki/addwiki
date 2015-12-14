<?php

namespace Mediawiki\Bot\Commands\Wikimedia;

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
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;

class WikidataFilmRefs extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->get( 'defaults.wiki' );
		$defaultUser = $this->appConfig->get( 'defaults.user' );

		$this
			->setName( 'wm:wd:refs:films' )
			->setDescription( 'Edits the page' )
			->addOption(
				'sourcewiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured wiki to find extensions on (A Wikibase Client)',
				$defaultWiki
			)
			->addOption(
				'targetwiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured wiki to acc data to (A Wikibase Repo)',
				$defaultWiki
			)
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED :
					InputOption::VALUE_OPTIONAL ),
				'The configured user to use',
				$defaultUser
			)
			->addOption(
				'title',
				null,
				InputOption::VALUE_OPTIONAL,
				'Which title do you want to use as a source'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( "THIS SCRIPT IS IN TESTING, if you use it it is your fault if anything goes wrong" );

		$sourceWiki = $input->getOption( 'sourcewiki' );
		$targetWiki = $input->getOption( 'targetwiki' );
		$user = $input->getOption( 'user' );

		$userDetails = $this->appConfig->get( 'users.' . $user );
		$sourceWikiDetails = $this->appConfig->get( 'wikis.' . $sourceWiki );
		$targetWikiDetails = $this->appConfig->get( 'wikis.' . $targetWiki );

		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		if ( $sourceWikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}
		if ( $targetWikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$pageIdentifier = null;
		if ( $input->getOption( 'title' ) != null ) {
			$sourceTitle = $input->getOption( 'title' );
			$pageIdentifier = new PageIdentifier( new Title( $sourceTitle ) );
		} else {
			throw new RuntimeException( 'No titles was set!' );
		}

		$sourceApi = new MediawikiApi( $sourceWikiDetails['url'] );
		$targetApi = new MediawikiApi( $targetWikiDetails['url'] );
		$output->writeln( "Logging in" );
		$loggedIn =
			$targetApi->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if ( !$loggedIn ) {
			$output->writeln( 'Failed to log in to target wiki' );

			return -1;
		}

		$sourceMwFactory = new MediawikiFactory( $sourceApi );
		$sourceParser = $sourceMwFactory->newParser();
		$output->writeln( "Parsing page" );
		$parseResult = $sourceParser->parsePage( $pageIdentifier );

		//Get the wikibase item if it exists
		$itemId = null;
		if ( array_key_exists( 'properties', $parseResult ) ) {
			foreach ( $parseResult['properties'] as $pageProp ) {
				if ( $pageProp['name'] == 'wikibase_item' ) {
					$itemId = new ItemId( $pageProp['*'] );
				}
			}
		}

		if ( $itemId === null ) {
			$output->writeln( "Could not find item for wikipage" );

			return -1;
		}

		$externalLinks = array();
		if ( array_key_exists( 'externallinks', $parseResult ) ) {
			foreach( $parseResult['externallinks'] as $externalLink ) {
				$externalLinks[] = $this->normalizeWikipediaExternalLink( $externalLink );
			}
		}

		if ( empty( $externalLinks ) ) {
			$output->writeln( "Could not find any external links for the given page" );

			return -1;
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

		$itemRevision = $targetWbFactory->newRevisionGetter()->getFromId( $itemId );
		/** @var Item|FingerprintProvider|StatementListProvider $item */
		$item = $itemRevision->getContent()->getData();
		$startItemHash = md5( serialize( $item ) );

		$movieMicrodatas = array();

		//Make a bunch of requests
		$guzzleClient = new Client();
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
		$output->writeln( 'Getting responses' );
		foreach ( $futureResponses as $link => $futureResponse ) {
			try {
				$md = new MicrodataPhp( array( 'html' => $futureResponse->getBody() ) );
			}
			catch ( Exception $e ) {
				$output->writeln( $e->getMessage() );
				continue;
			}

			$data = $md->obj();
			$addedForThisLink = 0;
			foreach ( $data->items as $microdata ) {
				//TODO also match https or protocol relative?
				if ( in_array( 'http://schema.org/Movie', $microdata->type ) ) {
					$microdata->url = $link;
					$movieMicrodatas[] = $microdata;
					$addedForThisLink++;
				}
			}
			$output->writeln(
				count( $data->items ) . " data items found, $addedForThisLink of use for $link"
			);
		}

		$output->writeln( "Got " . count( $movieMicrodatas ) . " microdata items" );

		foreach ( $movieMicrodatas as $dataObject ) {
			$sourceUrl = $dataObject->url;
			/** @var array $dataProperty */
			foreach ( $dataObject->properties as $dataPropertyName => $dataProperties ) {
				if ( $dataPropertyName == 'director' ) {
					foreach ( $dataProperties as $innerDataObject ) {
						if ( array_key_exists( 'properties', $innerDataObject ) ) {
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
										$directorItemRevision = $targetWbFactory->newRevisionGetter()->getFromId( $directorItemId->getEntityId() );
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
												$output->writeln( 'Adding a reference for ' . $sourceUrl );
												$directorStatement->addNewReference(
													// Source URL
													new PropertyValueSnak( new PropertyId( 'P854' ), new StringValue( $sourceUrl ) ),
													// Date retrieved
													new PropertyValueSnak( new PropertyId( 'P813' ), $this->getWikidataNowTimeValue() )
													// TODO date published?
												);
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

		//If the item has changed
		if( $startItemHash != md5( serialize( $item ) ) ) {
			$output->writeln( 'Trying to save!' );
			$targetWbFactory->newRevisionSaver()->save(
				new Revision(
					$itemRevision->getContent(),
					$itemRevision->getPageIdentifier()
				),
				new EditInfo( "Test import references from Wikipedia ($sourceWiki)" )
			);
		} else {
			$output->writeln( 'No changes were made!' );
		}

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
				$strings[] = $fingerprint->getAliasGroup( $lang )->getAliases();
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
