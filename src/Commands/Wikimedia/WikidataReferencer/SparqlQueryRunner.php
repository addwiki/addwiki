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

	public function getItemIdsForInstanceOf( ItemId $instanceOf ) {
		$queryBuilder = new QueryBuilder( array(
			'prov' => 'http://www.w3.org/ns/prov#',
			'wd' => 'http://www.wikidata.org/entity/',
			'wdt' => 'http://www.wikidata.org/prop/direct/',
			'p' => 'http://www.wikidata.org/prop/',
		) );
		$query = $queryBuilder
			->select( '?item' )
			->where( '?item', 'wdt:P31', 'wd:' . $instanceOf->getSerialization() )
			->__toString();
		return $this->getItemIdsFromQuery( $query );
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
