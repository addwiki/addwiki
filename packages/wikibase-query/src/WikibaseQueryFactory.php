<?php

namespace Addwiki\Wikibase\Query;

use Addwiki\Mediawiki\Api\Guzzle\ClientFactory;
use Addwiki\Wikibase\Query\Service\SimpleQueryService;
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

	public function newWikibaseQueryService(): WikibaseQueryService {
		return new WikibaseQueryService( $this->getClient(), $this->endpoint );
	}

	public function newQueryBuilderFactory(): QueryBuilderFactory {
		return new QueryBuilderFactory( $this->prefixMapping );
	}

	public function newSimpleQueryService(): SimpleQueryService {
		return new SimpleQueryService( $this->newWikibaseQueryService(), $this->newQueryBuilderFactory() );
	}

}
