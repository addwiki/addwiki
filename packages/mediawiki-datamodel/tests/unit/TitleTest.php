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
	 */
	public function testValidConstruction( string $title, int $ns ): void {
		$titleObj = new Title( $title, $ns );
		$this->assertEquals( $title, $titleObj->getText() );
		$this->assertEquals( $title, $titleObj->getTitle() );
		$this->assertEquals( $ns, $titleObj->getNs() );
	}

	public function provideValidConstruction(): array {
		return [
		[ 'fooo', 0 ],
		[ 'Foo:Bar', 15 ],
		[ 'FooBar:Bar', 9999 ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( string $title, int $ns ): void {
		$this->expectException( InvalidArgumentException::class );
		new Title( $title, $ns );
	}

	public function provideInvalidConstruction(): array {
		return [
		[ '', 1 ],
		];
	}

	public function testJsonRoundTrip(): void {
		$title = new Title( 'Foo', 19 );
		$json = $title->jsonSerialize();
		$this->assertEquals( $title, Title::jsonDeserialize( $json ) );
	}

}
