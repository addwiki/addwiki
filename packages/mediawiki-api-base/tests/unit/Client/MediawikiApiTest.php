<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client;

use Addwiki\Mediawiki\Api\Client\ApiUser;
use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use Addwiki\Mediawiki\Api\Client\UsageException;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use stdClass;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\MediawikiApi
 */
class MediawikiApiTest extends TestCase {

	/**
	 * @return string[][]
	 */
	public function provideValidConstruction(): array {
		return [
			[ 'localhost' ],
			[ 'http://en.wikipedia.org/w/api.php' ],
			[ '127.0.0.1/foo/bar/wwwwwwwww/api.php' ],
		];
	}

	/**
	 * @dataProvider provideValidConstruction
	 * @param string[] $apiLocation
	 */
	public function testValidConstruction( array $apiLocation ): void {
		new MediawikiApi( $apiLocation );
		$this->assertTrue( true );
	}

	public function provideInvalidConstruction(): array {
		return [
			[ null ],
			[ 12_345_678 ],
			[ [] ],
			[ new stdClass() ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param null[] $apiLocation
	 */
	public function testInvalidConstruction( array $apiLocation ): void {
		$this->expectException( InvalidArgumentException::class );
		new MediawikiApi( $apiLocation );
	}

	/**
	 * @return ClientInterface&MockObject
	 */
	private function getMockClient() {
		return $this->createMock( ClientInterface::class );
	}

	/**
	 * @return MockObject&ResponseInterface
	 */
	private function getMockResponse( $responseValue ) {
		$mock = $this->createMock( ResponseInterface::class );
		$mock
			->method( 'getBody' )
			->willReturn( json_encode( $responseValue, JSON_THROW_ON_ERROR ) );
		return $mock;
	}

	/**
	 * @return array<int|string mixed[]>
	 */
	private function getExpectedRequestOpts( $params, $paramsLocation ): array {
		return [
			$paramsLocation => array_merge( $params, [ 'format' => 'json' ] ),
			'headers' => [ 'User-Agent' => 'addwiki-mediawiki-client' ],
		];
	}

	public function testGetRequestThrowsUsageExceptionOnError(): void {
		$client = $this->getMockClient();
		$client->expects( $this->once() )
			->method( 'request' )
			->will( $this->returnValue(
				$this->getMockResponse( [ 'error' => [
					'code' => 'imacode',
					'info' => 'imamsg',
				] ] )
			) );
		$api = new MediawikiApi( '', $client );

		try{
			$api->getRequest( new SimpleRequest( 'foo' ) );
			$this->fail( 'No Usage Exception Thrown' );
		}
		catch ( UsageException $usageException ) {
			$this->assertEquals( 'imacode', $usageException->getApiCode() );
			$this->assertEquals( 'imamsg', $usageException->getRawMessage() );
		}
	}

	public function testPostRequestThrowsUsageExceptionOnError(): void {
		$client = $this->getMockClient();
		$client->expects( $this->once() )
			->method( 'request' )
			->will( $this->returnValue(
				$this->getMockResponse( [ 'error' => [
					'code' => 'imacode',
					'info' => 'imamsg',
				] ] )
			) );
		$api = new MediawikiApi( '', $client );

		try{
			$api->postRequest( new SimpleRequest( 'foo' ) );
			$this->fail( 'No Usage Exception Thrown' );
		}
		catch ( UsageException $e ) {
			$this->assertSame( 'imacode', $e->getApiCode() );
			$this->assertSame( 'imamsg', $e->getRawMessage() );
		}
	}

	/**
	 * @dataProvider provideActionsParamsResults
	 * @param string[]|array<string, string>[] $expectedResult
	 * @param string[]|array<string, string>[] $action
	 * @param string[]|array<int|string, int|string|mixed[]>[] $params
	 */
	public function testGetActionReturnsResult( array $expectedResult, array $action, array $params = [] ): void {
		$client = $this->getMockClient();
		$params = array_merge( [ 'action' => $action ], $params );
		$client->expects( $this->once() )
			->method( 'request' )
			->with( 'GET', null, $this->getExpectedRequestOpts( $params, 'query' ) )
			->will( $this->returnValue( $this->getMockResponse( $expectedResult ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->getRequest( new SimpleRequest( $action, $params ) );

		$this->assertEquals( $expectedResult, $result );
	}

	/**
	 * @dataProvider provideActionsParamsResults
	 * @param string[]|array<string, string>[] $expectedResult
	 * @param string[]|array<string, string>[] $action
	 * @param string[]|array<int|string, int|string|mixed[]>[] $params
	 */
	public function testPostActionReturnsResult( array $expectedResult, array $action, array $params = [] ): void {
		$client = $this->getMockClient();
		$params = array_merge( [ 'action' => $action ], $params );
		$client->expects( $this->once() )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( $expectedResult ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->postRequest( new SimpleRequest( $action, $params ) );

		$this->assertEquals( $expectedResult, $result );
	}

	/**
	 * @return resource|bool
	 */
	private function getNullFilePointer() {
		if ( !file_exists( '/dev/null' ) ) {
			// windows
			return fopen( 'NUL', 'r' );
		}
		return fopen( '/dev/null', 'r' );
	}

	public function testPostActionWithFileReturnsResult(): void {
		$dummyFile = $this->getNullFilePointer();
		$params = [
			'filename' => 'foo.jpg',
			'file' => $dummyFile,
		];
		$client = $this->getMockClient();
		$client->expects( $this->once() )->method( 'request' )->with(
				'POST',
				null,
				[
					'multipart' => [
						[ 'name' => 'action', 'contents' => 'upload' ],
						[ 'name' => 'filename', 'contents' => 'foo.jpg' ],
						[ 'name' => 'file', 'contents' => $dummyFile ],
						[ 'name' => 'format', 'contents' => 'json' ],
					],
					'headers' => [ 'User-Agent' => 'addwiki-mediawiki-client' ],
				]
			)->will( $this->returnValue( $this->getMockResponse( [ 'success ' => 1 ] ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->postRequest( new SimpleRequest( 'upload', $params ) );

		$this->assertEquals( [ 'success ' => 1 ], $result );
	}

	/**
	 * @return string[][]|array<int[]|string int[]|string[]|mixed[]>[][]|array<string, string>[][]
	 */
	public function provideActionsParamsResults(): array {
		return [
			[ [ 'key' => 'value' ], 'logout' ],
			[ [ 'key' => 'value' ], 'logout', [ 'param1' => 'v1' ] ],
			[ [ 'key' => 'value', 'key2' => 1212, [] ], 'logout' ],
		];
	}

	public function testGoodLoginSequence(): void {
		$client = $this->getMockClient();
		$user = new ApiUser( 'U1', 'P1' );
		$eq1 = [
			'action' => 'login',
			'lgname' => 'U1',
			'lgpassword' => 'P1',
		];
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $eq1, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( [ 'login' => [
				'result' => 'NeedToken',
				'token' => 'IamLoginTK',
			] ] ) ) );
		$params = array_merge( $eq1, [ 'lgtoken' => 'IamLoginTK' ] );
		$response = $this->getMockResponse( [ 'login' => [ 'result' => 'Success' ] ] );
		$client->expects( $this->at( 1 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $response ) );
		$api = new MediawikiApi( '', $client );

		$this->assertTrue( $api->login( $user ) );
		$this->assertSame( 'U1', $api->isLoggedin() );
	}

	public function testBadLoginSequence(): void {
		$client = $this->getMockClient();
		$user = new ApiUser( 'U1', 'P1' );
		$eq1 = [
			'action' => 'login',
			'lgname' => 'U1',
			'lgpassword' => 'P1',
		];
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $eq1, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( [ 'login' => [
				'result' => 'NeedToken',
				'token' => 'IamLoginTK',
			] ] ) ) );
		$params = array_merge( $eq1, [ 'lgtoken' => 'IamLoginTK' ] );
		$response = $this->getMockResponse( [ 'login' => [ 'result' => 'BADTOKENorsmthin' ] ] );
		$client->expects( $this->at( 1 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $response ) );
		$api = new MediawikiApi( '', $client );

		$this->expectException( UsageException::class );
		$api->login( $user );
	}

	public function testLogout(): void {
		$client = $this->getMockClient();
		$client->method( 'request' )
			->withConsecutive(
				[ 'POST', null, $this->getExpectedRequestOpts( [
					'action' => 'query',
					'meta' => 'tokens',
					'type' => 'csrf',
					'continue' => ''
				], 'form_params' ) ],
				[ 'POST', null, $this->getExpectedRequestOpts( [
					'action' => 'logout',
					'token' => 'TKN-csrf'
				], 'form_params' ) ]
			)
			->willReturnOnConsecutiveCalls(
				$this->returnValue( $this->getMockResponse( [
					'query' => [
						'tokens' => [
							'csrf' => 'TKN-csrf',
						]
					]
				] ) ),
				$this->returnValue( $this->getMockResponse( [] ) )
			);
		$api = new MediawikiApi( '', $client );

		$this->assertTrue( $api->logout() );
	}

	public function testLogoutOnFailure(): void {
		$client = $this->getMockClient();
		$client->method( 'request' )
			->withConsecutive(
				[ 'POST', null, $this->getExpectedRequestOpts( [
					'action' => 'query',
					'meta' => 'tokens',
					'type' => 'csrf',
					'continue' => ''
				], 'form_params' ) ],
				[ 'POST', null, $this->getExpectedRequestOpts( [
					'action' => 'logout',
					'token' => 'TKN-csrf'
				], 'form_params' ) ]
			)
			->willReturnOnConsecutiveCalls(
				$this->returnValue( $this->getMockResponse( [
					'query' => [
						'tokens' => [
							'csrf' => 'TKN-csrf',
						]
					]
				] ) ),
				$this->returnValue( $this->getMockResponse( null ) )
			);
		$api = new MediawikiApi( '', $client );

		$this->assertFalse( $api->logout() );
	}

	/**
	 * @dataProvider provideVersions
	 * @param string[] $apiValue
	 * @param string[] $expectedVersion
	 */
	public function testGetVersion( array $apiValue, array $expectedVersion ): void {
		$client = $this->getMockClient();
		$params = [ 'action' => 'query', 'meta' => 'siteinfo', 'continue' => '' ];
		$client->expects( $this->exactly( 1 ) )
			->method( 'request' )
			->with( 'GET', null, $this->getExpectedRequestOpts( $params, 'query' ) )
			->will( $this->returnValue( $this->getMockResponse( [
				'query' => [
					'general' => [
						'generator' => $apiValue,
					],
				],
			] ) ) );
		$api = new MediawikiApi( '', $client );
		$this->assertEquals( $expectedVersion, $api->getVersion() );
	}

	public function provideVersions(): array {
		return [
			[ 'MediaWiki 1.25wmf13', '1.25' ],
			[ 'MediaWiki 1.24.1', '1.24.1' ],
			[ 'MediaWiki 1.19', '1.19' ],
			[ 'MediaWiki 1.0.0', '1.0.0' ],
		];
	}

	public function testLogWarningsWithWarningsDeeperInTheArray(): void {
		$input = [
			'upload' => [
				'result' => 'Warning',
				'warnings' => [
					'duplicate-archive' => 'Test.jpg'
				],
				'fileKey' => '157pzg7r75j4.bs0wl9.15.jpg',
				'sessionKey' => '157pzg7r75j4.bs0wl9.15.jpg'
			]
		];

		$client = $this->getMockClient();
		$api = new MediawikiApi( '', $client );

		$logger = $this->createMock( LoggerInterface::class );
		$logger
			->expects( $this->once() )
			->method( 'warning' );

		$api->setLogger( $logger );

		// Make logWarnings() accessible so we can test it the easy way.
		$reflection = new ReflectionClass( get_class( $api ) );
		$method = $reflection->getMethod( 'logWarnings' );
		$method->setAccessible( true );

		$method->invokeArgs( $api, [ $input ] );
	}
}
