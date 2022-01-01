<?php

namespace Addwiki\Wikibase\Commands;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\AuthMethod;
use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Wikibase\Api\WikibaseFactory;
use Addwiki\Wikimedia\Api\WikimediaFactory;
use ArrayAccess;
use Asparagus\QueryBuilder;
use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @todo convert this script to be not wikidata specific....
 */
class WikibaseEntityStatementRemover extends Command {

	private ArrayAccess $appConfig;

	private ?WikibaseFactory $wikibaseFactory = null;

	private ?ActionApi $wikibaseApi = null;

	private ?SparqlQueryRunner $sparqlQueryRunner = null;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	private function setServices( $wikibaseApiUrl, $sparqlEndpoint, AuthMethod $auth ): void {
		$defaultGuzzleConf = [
			'headers' => [ 'User-Agent' => 'addwiki - Wikibase Statement Remover' ]
		];
		$guzzleClient = new Client( $defaultGuzzleConf );
		$this->sparqlQueryRunner = new SparqlQueryRunner(
			$guzzleClient,
			$sparqlEndpoint
		);

		// Make some assumptions for now that this will be run on Wikimedia sites?
		// Otherwise some more advanced dynamic configuration might be needed?
		$wmFactory = new WikimediaFactory();
		$this->wikibaseApi = $wmFactory->newMediawikiApiForURL( $wikibaseApiUrl, $auth );
		$this->wikibaseFactor = $wmFactory->newWikibaseFactoryForURL( $wikibaseApiUrl, $auth );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->offsetGet( 'defaults.wiki' );
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'wb:rm-statement' )
			->setDescription( 'Removes statements using the given property' )
			->addOption(
				'wiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL ),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'sparql',
				null,
				InputOption::VALUE_REQUIRED,
				'The SPARQL endpoint to use'
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
				'property',
				null,
				InputOption::VALUE_REQUIRED,
				'Property to target'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$user = $input->getOption( 'user' );
		$userDetails = $this->appConfig->offsetGet( 'users.' . $user );
		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		$wiki = $input->getOption( 'wiki' );
		$wikiDetails = $this->appConfig->offsetGet( 'wikis.' . $wiki );
		if ( $wikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$sparql = $input->getOption( 'sparql' );
		if ( $sparql === null || empty( $sparql ) ) {
			throw new RuntimeException( 'SPARQL endpoint must be passed' );
		}

		$this->setServices( $wikiDetails['url'], $sparql, new UserAndPassword( $userDetails['username'], $userDetails['password'] ) );

		$propertyString = $input->getOption( 'property' );
		$property = new PropertyId( $propertyString );
		if ( $propertyString === null || $propertyString === '' || $property === null ) {
			throw new RuntimeException( 'No property given' );
		}

		$output->writeln( 'Running SPARQL query to find items to check' );
		$queryBuilder = new QueryBuilder( [
			'wdt' => 'http://www.wikidata.org/prop/direct/',
		] );

		$itemIds = $this->sparqlQueryRunner->getItemIdsFromQuery(
			$queryBuilder
			->select( '?item' )
			->where( '?item', 'wdt:' . $propertyString, '?value' )
			->limit( 10000 )
			->__toString()
		);

		$itemLookup = $this->wikibaseFactory->newItemLookup();

		$statementRemover = $this->wikibaseFactory->newStatementRemover();

		foreach ( $itemIds as $itemId ) {
			$item = $itemLookup->getItemForId( $itemId );

			/** Suppressions can be removed once https://github.com/wmde/WikibaseDataModel/pull/838 is released */
			/** @psalm-suppress UndefinedDocblockClass */
			/** @psalm-suppress UndefinedClass */
			foreach ( $item->getStatements()->getIterator() as $statement ) {
				if ( $statement->getPropertyId()->equals( $property ) ) {

					$statementRemover->remove(
						$statement,
						new EditInfo(
							// TODO allow a user defined statement
							//TODO allow bot flag?
							'Removing Statement'
						)
					);

				}
			}
		}

		return 0;
	}
}
