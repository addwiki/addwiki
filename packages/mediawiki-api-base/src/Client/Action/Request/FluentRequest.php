<?php

namespace Addwiki\Mediawiki\Api\Client\Action\Request;

class FluentRequest implements Request {

	private array $params = [];
	private array $headers = [];
	private string $method;

	public function getMethod() : string {
		return $this->method;
	}

	public function setMethod( string $method ): void {
		$this->method = $method;
	}

	public function getParams(): array {
		return $this->params;
	}

	public function getHeaders(): array {
		return $this->headers;
	}

	public function getEncoding(): string {
		if ( $this->getMethod() === 'GET' ) {
			return self::ENCODING_QUERY;
		}
		return $this->getEncodingForPost();
	}

	private function getEncodingForPost(): string {
		if ( $this instanceof MultipartRequest || $this->paramsIncludesResource() ) {
			return self::ENCODING_MULTIPART;
		}
		return self::ENCODING_FORMPARAMS;
	}

	private function paramsIncludesResource(): bool {
		foreach ( $this->getParams() as $value ) {
			if ( is_resource( $value ) ) {
				return true;
			}
		}
		return false;
	}

	public static function factory() {
		return new static();
	}

	public function setAction( string $action ): self {
		$this->setParam( 'action', $action );
		return $this;
	}

	public function setParams( array $params ): self {
		$this->params = $params;
		return $this;
	}

	public function addParams( array $params ): self {
		$this->params = array_merge( $this->params, $params );
		return $this;
	}

	public function setParam( string $param, string $value ): self {
		$this->params[$param] = $value;
		return $this;
	}

	public function setHeaders( array $headers ): self {
		$this->headers = $headers;
		return $this;
	}

}
