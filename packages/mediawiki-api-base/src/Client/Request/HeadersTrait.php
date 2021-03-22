<?php

namespace Addwiki\Mediawiki\Api\Client\Request;

trait HeadersTrait {

	private array $headers = [];

	/**
	 * @return mixed[]
	 */
	public function getHeaders(): array {
		return $this->headers;
	}

	public function setHeaders( array $headers ): self {
		$this->headers = $headers;
		return $this;
	}

}
