<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration\Client\Auth;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\Auth\NoAuth;
use Addwiki\Mediawiki\Api\Tests\Integration\BaseTestEnvironment;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {

	/**
	 * @return mixed[]
	 */
	private function getUserInfo( ActionApi $api ) : array {
		return $api->request( ActionRequest::simpleGet( 'query', [ 'meta' => 'userinfo' ] ) );
	}

	private function assertUserLoggedIn( string $expectedUser, ActionApi $api ): void {
		$this->assertSame( $expectedUser, $this->getUserInfo( $api )['query']['userinfo']['name'] );
	}

	private function assertAnon( ActionApi $api ): void {
		$this->assertArrayHasKey( 'anon', $this->getUserInfo( $api )['query']['userinfo'] );
	}

	/**
	 * @return mixed[]
	 */
	private function getUserInfoUsingPost( ActionApi $api ) : array {
		return $api->request( ActionRequest::simplePost( 'query', [ 'meta' => 'userinfo' ] ) );
	}

	private function assertUserLoggedInUsingPost( string $expectedUser, ActionApi $api ): void {
		$this->assertSame( $expectedUser, $this->getUserInfoUsingPost( $api )['query']['userinfo']['name'] );
	}

	public function testNoAuth(): void {
		$this->assertAnon( BaseTestEnvironment::newInstance()->getActionApi( new NoAuth() ) );
	}

	public function testUsernamePasswordAuth(): void {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getUserAndPasswordAuth();
		$api = $env->getActionApi( $auth );
		$this->assertUserLoggedIn( $auth->getUsername(), $api );
	}

	public function testOAuthAuthGet(): void {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getOAuthOwnerConsumerAuth();
		$api = $env->getActionApi( $auth );
		$this->assertUserLoggedIn( 'CIUser', $api );
	}

	public function testOAuthAuthPost(): void {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getOAuthOwnerConsumerAuth();
		$api = $env->getActionApi( $auth );
		$this->assertUserLoggedInUsingPost( 'CIUser', $api );
	}

	public function testOAuthAuthPostMultipart(): void {
		$env = BaseTestEnvironment::newInstance();
		$auth = $env->getOAuthOwnerConsumerAuth();
		$api = $env->getActionApi( $auth );
		$multiRequest = new ActionRequest();
		$multiRequest->setMultipart( true );
		$multiRequest->setMethod( 'POST' );
		$multiRequest->setParams( [ 'action' => 'query', 'meta' => 'userinfo' ] );
		$this->assertSame( 'CIUser', $api->request( $multiRequest )['query']['userinfo']['name'] );
	}

}
