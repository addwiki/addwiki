<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client;

use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\SimpleRequest
 */
class SimpleRequestTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param string[]|mixed[][]|array<string, string>[] $action
	 * @param string[]|mixed[][]|array<string, string>[] $params
	 * @param string[]|array<string, string>[] $expected
	 * @param string[]|array<string, string>[] $headers
	 */
	public function testValidConstruction( array $action, array $params, array $expected, array $headers = [] ): void {
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

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param mixed[][] $action
	 */
	public function testInvalidConstruction( array $action, $params ): void {
		$this->expectException( InvalidArgumentException::class );
		new SimpleRequest( $action, $params );
	}

	/**
	 * @return mixed[][][]
	 */
	public function provideInvalidConstruction(): array {
		return [
			[ [], [] ],
		];
	}

}
