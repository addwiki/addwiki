<?php

namespace Addwiki\Mediawiki\Api\Client;

/**
 * Wrap a Request, adding more headers
 */
class HeaderWrappedRequest implements Request {

	private Request $request;
	private array $headers = [];

	public function __construct( Request $request, array $headers ) {
		$this->request = $request;
		$this->headers = $headers;
	}

	public function getParams(): array {
		return $this->request->getParams();
	}

	public function getHeaders(): array {
		return array_merge(
			$this->request->getHeaders(),
			$this->headers
		);
	}

}
