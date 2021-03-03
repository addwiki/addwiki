<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client;

use Addwiki\Mediawiki\Api\Client\Action\MediawikiSession;
use Addwiki\Mediawiki\Api\Client\Action\Request\SimpleRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mediawiki\Api\MediawikiSession
 */
class MediawikiSessionTest extends TestCase {

	/**
	 * @return MockObject|\Addwiki\Mediawiki\Api\Client\Action\MediawikiApi
	 */
	private function getMockApi() {
		return $this->createMock( \Addwiki\Mediawiki\Api\Client\Action\ActionApi::class );
	}

	public function testConstruction(): void {
		$session = new MediawikiSession( $this->getMockApi() );
		$this->assertInstanceOf( \Addwiki\Mediawiki\Api\Client\Action\MediawikiSession::class, $session );
	}

	/**
	 * @dataProvider provideTokenTypes
	 */
	public function testGetToken( string $tokenType ): void {
		$mockApi = $this->getMockApi();
		$mockApi->expects( $this->exactly( 2 ) )
			->method( 'postRequest' )
			->with( $this->isInstanceOf( SimpleRequest::class ) )
			->willReturn( [
				'query' => [
					'tokens' => [
					$tokenType => 'TKN-' . $tokenType,
					]
				]
			] );

		$session = new \Addwiki\Mediawiki\Api\Client\Action\MediawikiSession( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
		// Then clearing the tokens and calling again should make a second call!
		$session->clearTokens();
		$this->assertEquals( 'TKN-' . $tokenType, $session->getToken() );
	}

	/**
	 * @dataProvider provideTokenTypes
	 */
	public function testGetTokenPre125( string $tokenType ): void {
		$mockApi = $this->getMockApi();
		$mockApi->method( 'postRequest' )
			->with( $this->isInstanceOf( SimpleRequest::class ) )
			->willReturnOnConsecutiveCalls(
				[
					'warnings' => [
						'query' => [
							'*' => "Unrecognized value for parameter 'meta': tokens",
						]
					]
				],
				[
					'tokens' => [
						$tokenType => 'TKN-' . $tokenType,
					]
				]
			);

		$session = new MediawikiSession( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertSame( 'TKN-' . $tokenType, $session->getToken() );
		$this->assertSame( 'TKN-' . $tokenType, $session->getToken() );
	}

	public function provideTokenTypes(): array {
		return [
			[ 'csrf' ],
			[ 'edit' ],
		];
	}

}
