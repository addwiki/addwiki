<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use Asparagus\QueryBuilder;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @author Addshore
 * @todo factor this out into some library?
 */
class SparqlQueryRunner {

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @param Client $guzzleClient
	 */
	public function __construct( Client $guzzleClient ) {
		$this->client = $guzzleClient;
	}

	/**
	 * @param array $simpleQueryParts
	 *     eg. 'P1:Q2' OR 'P5:?'
	 *
	 * @return ItemId[]
	 */
	public function getItemIdsForSimpleQueryParts( array $simpleQueryParts ) {
		if( empty( $simpleQueryParts ) ) {
			throw new InvalidArgumentException( "Can't run a SPARQL query with no simple parts" );
		}

		$queryBuilder = new QueryBuilder( array(
			'prov' => 'http://www.w3.org/ns/prov#',
			'wd' => 'http://www.wikidata.org/entity/',
			'wdt' => 'http://www.wikidata.org/prop/direct/',
			'p' => 'http://www.wikidata.org/prop/',
		) );
		$queryBuilder->select( '?item' );
		foreach( $simpleQueryParts as $key => $simpleQueryPart ) {
			list( $propertyIdString, $entityIdString ) = explode( ':', $simpleQueryPart );
			if( $entityIdString == '?' ) {
				$queryBuilder->where( '?item', "wdt:$propertyIdString", '?' . str_repeat( 'z', $key ) );
			} else {
				$queryBuilder->where( '?item', "wdt:$propertyIdString", "wd:$entityIdString" );
			}
		}

		return $this->getItemIdsFromQuery( $queryBuilder->__toString() );
	}

	/**
	 * @param string $query
	 *
	 * @return ItemId[]
	 */
	public function getItemIdsFromQuery( $query ) {
		if( !is_string( $query ) ) {
			throw new InvalidArgumentException( "SPARQL query must be a string!" );
		}

		$sparqlResponse = $this->client->get(
			'https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query=' . urlencode( $query )
		);
		$sparqlArray = json_decode( $sparqlResponse->getBody(), true );

		$itemIds = array();
		foreach( $sparqlArray['results']['bindings'] as $binding ) {
			$itemIds[] = new ItemId( str_replace( 'http://www.wikidata.org/entity/', '', $binding['item']['value'] ) );
		}

		return $itemIds;
	}

}
