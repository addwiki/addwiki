<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mediawiki\DataModel\PageIdentifier
 */
class PageIdentifierTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( ?Title $title, ?int $pageid, bool $identifiesPage ): void {
		$pageIdentifier = new PageIdentifier( $title, $pageid );
		if ( is_string( $title ) ) {
			$this->assertEquals( new Title( $title ), $pageIdentifier->getTitle() );
		} else {
			$this->assertEquals( $title, $pageIdentifier->getTitle() );
		}
		$this->assertEquals( $pageid, $pageIdentifier->getId() );
		$this->assertEquals( $identifiesPage, $pageIdentifier->identifiesPage() );
	}

	public function provideValidConstruction(): array {
		return [
		[ null, null, false ],
		[ new Title( 'Foo' ), null, true ],
		[ new Title( 'Foo', 2 ), null, true ],
		[ null, 3, true ],
		];
	}

	public function provideRoundTripObjects(): array {
		return [
		[ new PageIdentifier( null, null ) ],
		[ new PageIdentifier( null, 44 ) ],
		[ new PageIdentifier( new Title( 'someTitle', 12 ), null ) ],
		[ new PageIdentifier( new Title( 'someTitle', 55 ), 99 ) ],
		];
	}

	/**
	 * @dataProvider provideRoundTripObjects
	 */
	public function testJsonRoundTrip( PageIdentifier $identifierObject ): void {
		$json = $identifierObject->jsonSerialize();
		$this->assertEquals( $identifierObject, PageIdentifier::jsonDeserialize( $json ) );
	}

}
