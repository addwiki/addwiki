<?php

namespace Addwiki\Mediawiki\Api\Client\Action\Request;

trait ParameterActionTrait {

	/**
	 * Must be used in conjunction with HasParameters
	 */
	public function setAction( string $action ): self {
		$this->setParam( 'action', $action );
		return $this;
	}

}
