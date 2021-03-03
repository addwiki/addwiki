<?php

namespace Addwiki\Mediawiki\Api\Client\Action;

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Common interface to be shared between APIs that allow making of requests.
 */
interface ApiRequester {

	/**
	 * @param ActionRequest $request The request to send.
	 *
	 * @return mixed Normally an array
	 */
	public function request( ActionRequest $request );

	/**
	 * @param ActionRequest $request The request to send.
	 *
	 *         Normally promising an array, though can be mixed (json_decode result)
	 */
	public function requestAsync( ActionRequest $request ): PromiseInterface;

}
