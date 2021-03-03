<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client;

use Addwiki\Mediawiki\Api\Client\Action\Tokens;
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
		$session = new Tokens( $this->getMockApi() );
		$this->assertInstanceOf( \Addwiki\Mediawiki\Api\Client\Action\Tokens::class, $session );
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

		$session = new \Addwiki\Mediawiki\Api\Client\Action\Tokens( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertEquals( 'TKN-' . $tokenType, $session->get() );
		$this->assertEquals( 'TKN-' . $tokenType, $session->get() );
		// Then clearing the tokens and calling again should make a second call!
		$session->clear();
		$this->assertEquals( 'TKN-' . $tokenType, $session->get() );
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

		$session = new Tokens( $mockApi );

		// Although we make 2 calls to the method we assert the tokens method about is only called once
		$this->assertSame( 'TKN-' . $tokenType, $session->get() );
		$this->assertSame( 'TKN-' . $tokenType, $session->get() );
	}

	public function provideTokenTypes(): array {
		return [
			[ 'csrf' ],
			[ 'edit' ],
		];
	}

}
