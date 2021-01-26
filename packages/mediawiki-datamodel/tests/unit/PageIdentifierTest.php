<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;

/**
 * @covers Mediawiki\DataModel\PageIdentifier
 */
class PageIdentifierTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $title, $pageid, $identifiesPage ) {
		$pageIdentifier = new PageIdentifier( $title, $pageid );
		if ( is_string( $title ) ) {
			$this->assertEquals( new Title( $title ), $pageIdentifier->getTitle() );
		} else {
			$this->assertEquals( $title, $pageIdentifier->getTitle() );
		}
		$this->assertEquals( $pageid, $pageIdentifier->getId() );
		$this->assertEquals( $identifiesPage, $pageIdentifier->identifiesPage() );
	}

	public function provideValidConstruction() {
		return [
		[ null, null, false ],
		[ new Title( 'Foo' ), null, true ],
		[ new Title( 'Foo', 2 ), null, true ],
		[ null, 3, true ],
		];
	}

	public function provideRoundTripObjects() {
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
	public function testJsonRoundTrip( PageIdentifier $identifierObject ) {
		$json = $identifierObject->jsonSerialize();
		$this->assertEquals( $identifierObject, PageIdentifier::jsonDeserialize( $json ) );
	}

}
