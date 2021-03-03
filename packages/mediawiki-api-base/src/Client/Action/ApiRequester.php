<?php

namespace Addwiki\Mediawiki\Api\Client\Action;

use Addwiki\Mediawiki\Api\Client\Action\Request\Request;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Common interface to be shared between APIs that allow making of requests.
 */
interface ApiRequester {

	/**
	 * @param Request $request The request to send.
	 *
	 * @return mixed Normally an array
	 */
	public function request( Request $request );

	/**
	 * @param Request $request The request to send.
	 *
	 *         Normally promising an array, though can be mixed (json_decode result)
	 */
	public function requestAsync( Request $request ): PromiseInterface;

}
