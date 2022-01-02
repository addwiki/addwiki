<?php

namespace Addwiki\Wikibase\Query;

use Addwiki\Mediawiki\Api\Guzzle\ClientFactory;
use Addwiki\Wikibase\Commands\SparqlQueryRunner;
use GuzzleHttp\ClientInterface;

/**
 * @access public
 */
class WikibaseQueryFactory {

	private $endpoint;
	private $prefixMapping;
	private $client;

	public function __construct(
		string $sparqlEndpoint,
		array $prefixMapping = [],
		ClientInterface $client = null
		) {
		$this->endpoint = $sparqlEndpoint;
		$this->prefixMapping = $prefixMapping;
		$this->client = $client;
	}

	private function getClient(): ClientInterface {
		if ( !$this->client instanceof ClientInterface ) {
			$clientFactory = new ClientFactory();
			// $clientFactory->setLogger( $this->logger );
			$this->client = $clientFactory->getClient();
		}

		return $this->client;
	}

	public function newSparqlQueryRunner(): SparqlQueryRunner {
		return new SparqlQueryRunner( $this->getClient(), $this->endpoint );
	}

	public function newQueryBuilderFactory(): QueryBuilderFactory {
		return new QueryBuilderFactory( $this->prefixMapping );
	}

}
