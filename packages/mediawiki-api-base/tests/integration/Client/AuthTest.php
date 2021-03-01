<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration\Client;

use Addwiki\Mediawiki\Api\Client\Auth\NoAuth;
use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use Addwiki\Mediawiki\Api\Tests\Integration\BaseTestEnvironment;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {

	private function getUserInfo( MediaWikiApi $api ) : array {
		return $api->getRequest( new SimpleRequest( 'query', [ 'meta' => 'userinfo' ] ) );
	}

	private function assertUserLoggedIn( string $expectedUser, MediawikiApi $api ) {
		$this->assertSame( $expectedUser, $this->getUserInfo( $api )['query']['userinfo']['name'] );
	}

	private function assertAnon( MediawikiApi $api ) {
		$this->assertArrayHasKey( 'anon', $this->getUserInfo( $api )['query']['userinfo'] );
	}

	public function testNoAuth() {
		$this->assertAnon( BaseTestEnvironment::newInstance()->getApi( new NoAuth() ) );
	}

	public function testUsernamePasswordAuth() {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getUserAndPasswordAuth();
		$api = $env->getApi( $auth );
		$this->assertUserLoggedIn( $auth->getUsername(), $api );
	}

	public function testOAuthAuth() {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getOAuthOwnerConsumerAuth();
		$api = $env->getApi( $auth );
		$this->assertUserLoggedIn( 'CIUser', $api );
	}

}
