<?php

namespace Addwiki\Mediawiki\Api\Service;

use Addwiki\Mediawiki\Api\Client\Request\SimpleRequest;
use Addwiki\Mediawiki\DataModel\Page;
use Addwiki\Mediawiki\DataModel\Title;
use OutOfBoundsException;

/**
 * @access private
 */
class PageRestorer extends Service {

	/**
	 * @param Page $page
	 * @param array $extraParams
	 */
	public function restore( Page $page, array $extraParams = [] ): bool {
		$this->api->postRequest(
			new SimpleRequest(
				'undelete',
				$this->getUndeleteParams( $page->getTitle(), $extraParams )
			)
		);

		return true;
	}

	/**
	 * @return mixed[]
	 */
	private function getUndeleteParams( Title $title, array $extraParams ): array {
		$params = [];

		$params['title'] = $title->getTitle();
		$params['token'] = $this->getUndeleteToken( $title );

		return array_merge( $extraParams, $params );
	}

	/**
	 * @param Title $title
	 *
	 * @throws OutOfBoundsException
	 * @return mixed|void
	 */
	private function getUndeleteToken( Title $title ) {
		$response = $this->api->postRequest(
			new SimpleRequest(
				'query', [
				'list' => 'deletedrevs',
				'titles' => $title->getTitle(),
				'drprop' => 'token',
			]
			)
		);
		if ( array_key_exists( 'token', $response['query']['deletedrevs'][0] ) ) {
			return $response['query']['deletedrevs'][0]['token'];
		} else {
			throw new OutOfBoundsException(
				'Could not get page undelete token from list=deletedrevs query'
			);
		}
	}

}
