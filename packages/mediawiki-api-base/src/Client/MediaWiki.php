<?php

namespace Addwiki\Mediawiki\Api\Client;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\AuthMethod;
use Addwiki\Mediawiki\Api\Client\Auth\NoAuth;
use Addwiki\Mediawiki\Api\Client\Discovery\ReallySimpleDiscovery;

/**
 * Client encompassing both REST and Action MediaWiki APIs
 */
class MediaWiki {

	private const ACTION_PHP = 'api.php';

	private string $baseUrl;
	private AuthMethod $auth;

	private ActionApi $action;

	public function __construct( string $baseUrl, AuthMethod $auth = null ) {
		if ( $auth === null ) {
			$auth = new NoAuth();
		}
		$this->baseUrl = $baseUrl;
		$this->auth = $auth;
	}

	/**
	 * @param string $anApiEndpoint Either the REST or Action API endpoint e.g. https://en.wikipedia.org/w/api.php
	 * @param AuthMethod|null $auth
	 * @return self
	 */
	public static function newFromEndpoint( string $anApiEndpoint, AuthMethod $auth = null ): self {
		return new self( self::pruneActionOrRestPhp( $anApiEndpoint ), $auth );
	}

	private static function pruneActionOrRestPhp( string $url ): string {
		return str_replace( 'rest.php', '', str_replace( self::ACTION_PHP, '', $url ) );
	}

	/**
	 * @param string $anApiEndpoint A page on a MediaWiki site e.g. https://en.wikipedia.org/wiki/Main_Page
	 * @param AuthMethod|null $auth
	 * @return self
	 */
	public static function newFromPage( string $pageUrl, AuthMethod $auth = null ): self {
		return new self( ReallySimpleDiscovery::baseFromPage( $pageUrl ), $auth );
	}

	public function action(): ActionApi {
		if ( !$this->action ) {
			$this->action = new ActionApi( $this->baseUrl . self::ACTION_PHP, $this->auth );
		}
		return $this->action;
	}

	public function rest() {
		// TODO when implementing REST
	}

}
