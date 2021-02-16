<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Revisions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\Revisions
 * @author Addshore
 */
class RevisionsTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $input, $expected ) {
		$revisions = new Revisions( $input );
		$this->assertEquals( $expected, $revisions->toArray() );
	}

	public function provideValidConstruction() {
		$mockContent = $this->getMockBuilder( Content::class )
			->disableOriginalConstructor()
			->getMock();

		// todo mock these
		$rev1 = new Revision( $mockContent, new PageIdentifier( null, 1 ), 1 );
		$rev2 = new Revision( $mockContent, new PageIdentifier( null, 1 ), 2 );
		$rev4 = new Revision( $mockContent, new PageIdentifier( null, 1 ), 4 );

		return [
		[ [ $rev1 ], [ 1 => $rev1 ] ],
		[ [ $rev2, $rev1 ], [ 1 => $rev1, 2 => $rev2 ] ],
		[ [ $rev4, $rev1 ], [ 1 => $rev1, 4 => $rev4 ] ],
		[ new Revisions( [ $rev4, $rev1 ] ), [ 1 => $rev1, 4 => $rev4 ] ],
		];
	}

}
