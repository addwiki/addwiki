<?php

namespace Addwiki\Mediawiki\Api\Client;

interface Request {

	/**
	 * @return mixed[]
	 */
	public function getParams(): array;

	/**
	 * Associative array of headers to add to the request.
	 * Each key is the name of a header, and each value is a string or array of strings representing
	 * the header field values.
	 *
	 * @return mixed[]
	 */
	public function getHeaders(): array;

	public function setAction( string $action ): self;

	public function setParams( array $params ): self;

	public function addParams( array $params ): self;

	public function setParam( string $param, string $value ): self;

	public function setHeaders( array $headers ): self;

}
