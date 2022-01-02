<?php

namespace Addwiki\Wikibase\Query;

use GuzzleHttp\Client;

class WikibaseQueryService {

	private Client $client;

	private string $sparqlEndpoint;

	/**
	 * @param string $sparqlEndpoint eg. 'https://query.wikidata.org/bigdata/namespace/wdq/sparql'
	 */
	public function __construct( Client $guzzleClient, string $sparqlEndpoint ) {
		$this->client = $guzzleClient;
		$this->sparqlEndpoint = $sparqlEndpoint;
	}

	public function query( string $query ): array {
		$sparqlResponse = $this->client->get(
			$this->sparqlEndpoint . '?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );
		// See $sparqlArray['results']['bindings']
		return $sparqlArray;
	}

}
