<?php

namespace Addwiki\Wikibase\Commands;

use Asparagus\QueryBuilder;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @author Addshore
 * @todo factor this out into some library? YES (Now used in 2 separate places...)
 */
class SparqlQueryRunner {

	private Client $client;

	private string $sparqlEndpoint;

	/**
	 * @param string $sparqlEndpoint eg. 'https://query.wikidata.org/bigdata/namespace/wdq/sparql'
	 */
	public function __construct( Client $guzzleClient, string $sparqlEndpoint ) {
		$this->client = $guzzleClient;
		$this->sparqlEndpoint = $sparqlEndpoint;
	}

	/**
	 * @param array $simpleQueryParts
	 *     eg. 'P1:Q2' OR 'P5:?'
	 *
	 * @return ItemId[]
	 */
	public function getItemIdsForSimpleQueryParts( array $simpleQueryParts ): array {
		if ( empty( $simpleQueryParts ) ) {
			throw new InvalidArgumentException( "Can't run a SPARQL query with no simple parts" );
		}

		$queryBuilder = new QueryBuilder( [
			'prov' => 'http://www.w3.org/ns/prov#',
			'wd' => 'http://www.wikidata.org/entity/',
			'wdt' => 'http://www.wikidata.org/prop/direct/',
			'p' => 'http://www.wikidata.org/prop/',
		] );
		$queryBuilder->select( '?item' );
		foreach ( $simpleQueryParts as $key => $simpleQueryPart ) {
			[ $propertyIdString, $entityIdString ] = explode( ':', $simpleQueryPart );
			if ( $entityIdString == '?' ) {
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), '?' . str_repeat( 'z', $key ) );
			} else {
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), sprintf( 'wd:%s', $entityIdString ) );
			}
		}

		return $this->getItemIdsFromQuery( $queryBuilder->__toString() );
	}

	/**
	 *
	 * @return ItemId[]
	 */
	public function getItemIdsFromQuery( string $query ): array {
		if ( !is_string( $query ) ) {
			throw new InvalidArgumentException( "SPARQL query must be a string!" );
		}

		$sparqlResponse = $this->client->get(
			$this->sparqlEndpoint . '?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );

		$itemIds = [];
		foreach ( $sparqlArray['results']['bindings'] as $binding ) {
			// TODO this might have to cope with more than just wikidata.org things?
			$itemIds[] = new ItemId( str_replace( 'http://www.wikidata.org/entity/', '', $binding['item']['value'] ) );
		}

		return $itemIds;
	}

}
