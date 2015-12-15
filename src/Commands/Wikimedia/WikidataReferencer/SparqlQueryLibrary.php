<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use Asparagus\QueryBuilder;
use RuntimeException;

/**
 * @author AddShore
 */
class SparqlQueryLibrary {

	/**
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	public function __construct() {
		$this->queryBuilder = new QueryBuilder( array(
			'prov' => 'http://www.w3.org/ns/prov#',
			'wd' => 'http://www.wikidata.org/entity/',
			'wdt' => 'http://www.wikidata.org/prop/direct/',
			'p' => 'http://www.wikidata.org/prop/',
		) );
	}

	/**
	 * @param string $type schema.org type eg. "http://schema.org/Movie"
	 *
	 * Queries here should always select an ?item
	 *
	 * @return string
	 */
	public function getQueryForSchemaType( $type ) {
		if( $type === 'Movie' ) {
			return $this->queryBuilder
				->select( '?item' )
				->where( '?item', 'wdt:P31', 'wd:Q11424' )
				->also( '?item', 'wdt:P57', '?director' )
				//NOTE: does this filter even exist?
				->filterNotExists( '?director', 'prov:wasDerivedFrom', '?somewhere' )
				->__toString();
		}
		throw new RuntimeException( "Query not known for type: " . $type );
	}

}
