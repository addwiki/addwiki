<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client\Action\Request;

use Addwiki\Mediawiki\Api\Client\Action\Request\SimpleRequest;
use PHPUnit\Framework\TestCase;

class SimpleRequestTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( string $action, array $params, array $expected, array $headers = [] ): void {
		$request = new SimpleRequest( $action, $params, $headers );
		$this->assertEquals( $expected, $request->getParams() );
		$this->assertEquals( $headers, $request->getHeaders() );
	}

	public function provideValidConstruction(): array {
		return [
			[ 'action', [], [ 'action' => 'action' ] ],
			[ '1123', [], [ 'action' => '1123' ] ],
			[ 'a', [ 'b' => 'c' ], [ 'action' => 'a', 'b' => 'c' ] ],
			[ 'a', [ 'b' => 'c', 'd' => 'e' ], [ 'action' => 'a', 'b' => 'c', 'd' => 'e' ] ],
			[ 'a', [ 'b' => 'c|d|e|f' ], [ 'action' => 'a', 'b' => 'c|d|e|f' ] ],
			[ 'foo', [], [ 'action' => 'foo' ] ,[ 'foo' => 'bar' ] ],
		];
	}

}
