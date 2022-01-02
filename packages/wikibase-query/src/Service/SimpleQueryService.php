<?php

namespace Addwiki\Wikibase\Query\Service;

use Addwiki\Wikibase\Query\QueryBuilderFactory;
use Addwiki\Wikibase\Query\WikibaseQueryService;

/**
 * This service makes the assumption that a wdt: prefix is deifned and known by the query builder.
 */
class SimpleQueryService {

	private $wikibaseQueryService;
	private $queryBuilderFactory;

	public function __construct(
		WikibaseQueryService $wikibaseQueryService,
		QueryBuilderFactory $queryBuilderFactory
		) {
		$this->wikibaseQueryService = $wikibaseQueryService;
		$this->queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * @param string[] $simpleQuery eg. 'P1:Q2' OR 'P5:?'
	 * @return string[] string entitiy ids
	 */
	public function query( array $simpleQuery ): array {
		if ( empty( $simpleQuery ) ) {
			throw new \InvalidArgumentException( "Can't run a SPARQL query with no simple parts" );
		}

		$queryBuilder = $this->queryBuilderFactory->newQueryBuilder();
		$queryBuilder->select( '?item' );
		foreach ( $simpleQuery as $key => $simpleQueryPart ) {
			[ $propertyIdString, $entityIdString ] = explode( ':', $simpleQueryPart );
			if ( $entityIdString == '?' ) {
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), '?' . str_repeat( 'z', $key ) );
			} else {
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), sprintf( 'wd:%s', $entityIdString ) );
			}
		}

		return $this->getIdsFromQuery( $queryBuilder->__toString() );
	}

	/**
	 * @return string[]
	 */
	private function getIdsFromQuery( string $query ): array {
		$sparqlArray = $this->wikibaseQueryService->query( $query );

		$ids = [];
		foreach ( $sparqlArray['results']['bindings'] as $binding ) {
			$ids[] = $this->getLastPartOfUrlPath( $binding['item']['value'] );
		}

		return $ids;
	}

	private function getLastPartOfUrlPath( string $urlPath ): string {
		// Assume that the last part is always the ID?
		$parts = explode( '/', $urlPath );
		return end( $parts );
	}

}
