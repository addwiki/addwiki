<?php

namespace Addwiki\Mediawiki\Api\Client\Action\Request;

class ActionRequestFactory {

	public static function get( string $action, array $params = [], array $headers = [] ): FluentRequest {
		$req = new FluentRequest();
		$req->setMethod( 'GET' );
		$req->setAction( $action );
		$req->addParams( $params );
		$req->setHeaders( $headers );
		return $req;
	}

	public static function post( string $action, array $params = [], array $headers = [] ): FluentRequest {
		$req = new FluentRequest();
		$req->setMethod( 'POST' );
		$req->setAction( $action );
		$req->addParams( $params );
		$req->setHeaders( $headers );
		return $req;
	}

}
