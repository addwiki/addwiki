<?php

namespace Addwiki\Wikibase\Query;

use Asparagus\QueryBuilder;

/**
 * @access public
 */
class QueryBuilderFactory {

	private $prefixMapping;

	public function __construct( array $prefixMapping ) {
		$this->prefixMapping = $prefixMapping;
	}

	public function newQueryBuilder(): QueryBuilder {
		return new QueryBuilder( $this->prefixMapping );
	}

}
