<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Title;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Title
 * @author Addshore
 */
class TitleTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param int[]|string[] $title
	 * @param int[]|string[] $ns
	 */
	public function testValidConstruction( array $title, array $ns ): void {
		$titleObj = new Title( $title, $ns );
		$this->assertEquals( $title, $titleObj->getText() );
		$this->assertEquals( $title, $titleObj->getTitle() );
		$this->assertEquals( $ns, $titleObj->getNs() );
	}

	/**
	 * @return int[][]|string[][]
	 */
	public function provideValidConstruction(): array {
		return [
		[ 'fooo', 0 ],
		[ 'Foo:Bar', 15 ],
		[ 'FooBar:Bar', 9999 ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param mixed[][] $title
	 * @param string[]|mixed[][] $ns
	 */
	public function testInvalidConstruction( array $title, array $ns ): void {
		$this->expectException( InvalidArgumentException::class );
		new Title( $title, $ns );
	}

	public function provideInvalidConstruction(): array {
		return [
		[ [], [] ],
		[ 'foo', [] ],
		[ [], 1 ],
		[ null, 1 ],
		[ null, null ],
		[ 'foo', null ],
		];
	}

	public function testJsonRoundTrip(): void {
		$title = new Title( 'Foo', 19 );
		$json = $title->jsonSerialize();
		$this->assertEquals( $title, Title::jsonDeserialize( $json ) );
	}

}
