<?php

namespace Addwiki\Mediawiki\Api\Client\Action\Request;

use Addwiki\Mediawiki\Api\Client\Request\HasSimpleFactory;
use Addwiki\Mediawiki\Api\Client\Request\HeadersTrait;
use Addwiki\Mediawiki\Api\Client\Request\MethodTrait;
use Addwiki\Mediawiki\Api\Client\Request\ParametersTrait;
use Addwiki\Mediawiki\Api\Client\Request\Request;
use Addwiki\Mediawiki\Api\Client\Request\SimpleFactoryTrait;

class ActionRequest implements Request, HasSimpleFactory, HasParameterAction {

	use SimpleFactoryTrait;
	use MethodTrait;
	use HeadersTrait;
	use ParametersTrait;
	use ParameterActionTrait;

	public static function simpleGet( string $action, array $params = [], array $headers = [] ): self {
		$req = new self();
		$req->setMethod( 'GET' );
		$req->setAction( $action );
		$req->addParams( $params );
		$req->setHeaders( $headers );
		return $req;
	}

	public static function simplePost( string $action, array $params = [], array $headers = [] ): self {
		$req = new self();
		$req->setMethod( 'POST' );
		$req->setAction( $action );
		$req->addParams( $params );
		$req->setHeaders( $headers );
		return $req;
	}

}
