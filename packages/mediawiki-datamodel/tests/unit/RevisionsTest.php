<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Content;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revision;
use Addwiki\Mediawiki\DataModel\Revisions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Revisions
 * @author Addshore
 */
class RevisionsTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param Revision[]|Revisions $input
	 * @param array<int, Revision>[]|Revision[][] $expected
	 */
	public function testValidConstruction( $input, array $expected ): void {
		$revisions = new Revisions( $input );
		$this->assertEquals( $expected, $revisions->toArray() );
	}

	public function provideValidConstruction(): array {
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
