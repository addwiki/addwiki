<?php

namespace Addwiki\Wikibase\Api\Service;

use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Wikibase\Api\WikibaseApi;
use UnexpectedValueException;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementGuid;

/**
 * @access private
 *
 * @author Addshore
 */
class StatementRemover {

	/**
	 * @var WikibaseApi
	 */
	private $api;

	/**
	 * @param WikibaseApi $api
	 */
	public function __construct( WikibaseApi $api ) {
		$this->api = $api;
	}

	/**
	 * @since 0.2
	 *
	 * @param Statement|StatementGuid|string $statement Statement object or GUID
	 * @param EditInfo|null $editInfo
	 *
	 * @return bool
	 * @throws UnexpectedValueException
	 */
	public function remove( $statement, EditInfo $editInfo = null ) {
		if ( is_string( $statement ) ) {
			$guid = $statement;
		} elseif ( $statement instanceof StatementGuid ) {
			$guid = $statement->getSerialization();
		} elseif ( $statement instanceof Statement ) {
			$guid = $statement->getGuid();
		} else {
			throw new UnexpectedValueException( 'Could not get statement guid from $statement' );
		}
		if ( !is_string( $guid ) ) {
			throw new UnexpectedValueException( 'Unexpected statement guid got from $statement' );
		}

		$params = [
			'claim' => $guid,
		];

		$this->api->postRequest( 'wbremoveclaims', $params, $editInfo );
		return true;
	}

}
