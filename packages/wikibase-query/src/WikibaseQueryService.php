<?php

namespace Addwiki\Wikibase\Query;

use GuzzleHttp\ClientInterface;

class WikibaseQueryService {

	private ClientInterface $client;

	private string $sparqlEndpoint;

	/**
	 * @param string $sparqlEndpoint eg. 'https://query.wikidata.org/bigdata/namespace/wdq/sparql'
	 */
	public function __construct( ClientInterface $guzzleClient, string $sparqlEndpoint ) {
		$this->client = $guzzleClient;
		$this->sparqlEndpoint = $sparqlEndpoint;
	}

	/**
	 * Perform a SPARQL query against the SPARQL endpoint.
	 *
	 * @param string $query The full SPARQL query to perform.
	 * @return array Full response, that will look something like [ 'results' => [ 'bindings' => ... ] ]
	 */
	public function query( string $query ): array {
		$sparqlResponse = $this->client->get(
			$this->sparqlEndpoint . '?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );
		return $sparqlArray;
	}

	/**
	 * Extracts the suffixes from multiple keys of a query result, returning them in an array.
	 * This can be used to extract multiple entity IDS from full concept URIs.
	 * (Assuming the ID is always after the last /)
	 *
	 * @param array $queryResult From the query() method
	 * @param string[] $namedValues The keys of the value to extract, if your query has "?item" this would be "item" eg. [ 'item', 'item2' ]
	 * @return array[] The extracted values eg. [ [ 'item' => 'Q123', 'item2' => 'Q513' ], [ 'item' => 'Q999', 'item2' => 'Q500' ] ]
	 */
	public function getConceptSuffixesFromQueryResultMulti( array $queryResult, array $namedValues ): array {
		$map = [];
		foreach ( $queryResult['results']['bindings'] as $binding ) {
			$values = [];
			foreach ( $namedValues as $namedValue ) {
				if ( array_key_exists( $namedValue, $binding ) ) {
					$values[$namedValue] = $this->getLastPartOfUrlPath( $binding[$namedValue]['value'] );
				}
			}
			$map[] = $values;
		}
		return $map;
	}

	/**
	 * Extracts the suffixes from a specific key of a query result, returning them in an array.
	 * This can be used to extract entity IDS from full concept URIs.
	 * (Assuming the ID is always after the last /)
	 *
	 * @param array $queryResult From the query() method
	 * @param string $namedValue The key of the value to extract, if your query has "?item" this would be "item"
	 * @return string[] The extracted values eg. ['Q123', 'Q456']
	 */
	public function getConceptSuffixesFromQueryResult( array $queryResult, string $namedValue ): array {
		$ids = [];
		foreach ( $queryResult['results']['bindings'] as $binding ) {
			if ( array_key_exists( $namedValue, $binding ) ) {
				$ids[] = $this->getLastPartOfUrlPath( $binding[$namedValue]['value'] );
			}
		}
		return $ids;
	}

	/**
	 * @param string $urlPath eg. 'https://www.wikidata.org/wiki/Q123'
	 * @return string eg. 'Q123'
	 */
	private function getLastPartOfUrlPath( string $urlPath ): string {
		// Assume that the last part is always the ID?
		$parts = explode( '/', $urlPath );
		return end( $parts );
	}

}
