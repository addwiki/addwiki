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
	 * @param string[] $simpleQuery eg. 'P31:Q765' OR 'P10:?'
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
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), '?' . str_repeat( 'z', $key + 1 ) );
			} else {
				$queryBuilder->where( '?item', sprintf( 'wdt:%s', $propertyIdString ), sprintf( 'wd:%s', $entityIdString ) );
			}
		}

		$queryResult = $this->wikibaseQueryService->query( $queryBuilder->__toString() );
		return $this->wikibaseQueryService->getConceptSuffixesFromQueryResult( $queryResult, 'item' );
	}

}
