<?php

namespace Addwiki\Wikimedia\Commands;

use Asparagus\QueryBuilder;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @todo factor this out into some library?
 */
class SparqlQueryRunner {

	private Client $client;

	public function __construct( Client $guzzleClient ) {
		$this->client = $guzzleClient;
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
		$sparqlResponse = $this->client->get(
			'https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );

		$itemIds = [];
		foreach ( $sparqlArray['results']['bindings'] as $binding ) {
			$itemIds[] = new ItemId( str_replace( 'http://www.wikidata.org/entity/', '', $binding['item']['value'] ) );
		}

		return $itemIds;
	}

	/**
	 * @return mixed[]
	 */
	public function getItemIdStringsAndLabelsFromInstanceOf( $instanceItemIdString ): array {
		// TODO fix this ugliness
		$query = "PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wikibase: <http://wikiba.se/ontology#>
SELECT ?item ?itemLabel WHERE {
   ?item wdt:P31 wd:" . $instanceItemIdString . " .
   SERVICE wikibase:label {
    bd:serviceParam wikibase:language 'en' .
   }
 }";

		$sparqlResponse = $this->client->get(
			'https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );

		$data = [];
		foreach ( $sparqlArray['results']['bindings'] as $binding ) {
			$data[str_replace( 'http://www.wikidata.org/entity/', '', $binding['item']['value'] )] =
				$binding['itemLabel']['value'];
		}

		return $data;
	}

}
