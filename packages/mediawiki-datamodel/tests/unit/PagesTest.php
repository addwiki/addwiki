<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Page;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Pages;
use Addwiki\Mediawiki\DataModel\Revisions;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Pages
 * @author Addshore
 */
class PagesTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $input, array $expected ): void {
		$pages = new Pages( $input );
		$this->assertEquals( $expected, $pages->toArray() );
	}

	public function provideValidConstruction(): array {
		$mockTitle = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();
		$mockRevisions = $this->getMockBuilder( Revisions::class )
			->disableOriginalConstructor()
			->getMock();

		// todo mock these
		$page1 = new Page( new PageIdentifier( $mockTitle, 1 ), $mockRevisions );
		$page2 = new Page( new PageIdentifier( $mockTitle, 2 ), $mockRevisions );
		$page4 = new Page( new PageIdentifier( $mockTitle, 4 ), $mockRevisions );

		return [
		[ [ $page1 ], [ 1 => $page1 ] ],
		[ [ $page2, $page1 ], [ 1 => $page1, 2 => $page2 ] ],
		[ [ $page4, $page1 ], [ 1 => $page1, 4 => $page4 ] ],
		[ new Pages( [ $page4, $page1 ] ), [ 1 => $page1, 4 => $page4 ] ],
		];
	}

}
