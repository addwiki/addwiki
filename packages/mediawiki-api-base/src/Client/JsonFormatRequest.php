<?php

namespace Addwiki\Mediawiki\Api\Client;

class JsonFormatRequest implements Request {

	private Request $request;

	public function __construct( Request $request ) {
		$this->request = $request;
	}

	public function getParams(): array {
		return array_merge(
			$this->request->getParams(),
			[ 'format' => 'json' ]
		);
	}

	public function getHeaders(): array {
		return $this->request->getHeaders();
	}

}
