<?php

namespace Addwiki\Mediawiki\Api\Client\Request;

/**
 * Must be used in conjunction with HasMethod
 */
trait ParametersTrait {

	private array $params = [];

	public function getParams(): array {
		return $this->params;
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

	public function getParameterEncoding(): string {
		if ( $this->getMethod() === 'GET' ) {
			return self::ENCODING_QUERY;
		}
		return $this->getParameterEncodingForPost();
	}

	private function getParameterEncodingForPost(): string {
		if (
			( method_exists( $this, 'hasMultipartParams' ) && $this->hasMultipartParams() ) ||
			$this->paramsIncludesResource()
		) {
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

}
