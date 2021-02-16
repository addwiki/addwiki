<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revisions;
use Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\Page
 * @author Addshore
 */
class PageTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $pageIdentifier, $revisions ) {
		$page = new Page( $pageIdentifier, $revisions );
		$this->assertEquals( $pageIdentifier, $page->getPageIdentifier() );
		if ( $revisions === null ) {
			$this->assertInstanceOf( Revisions::class, $page->getRevisions() );
		} else {
			$this->assertEquals( $revisions, $page->getRevisions() );
		}
	}

	public function provideValidConstruction() {
		return [
		[ null, null ],
		[ null, $this->newMockRevisions() ],
		[ new PageIdentifier( $this->newMockTitle(), 1 ), $this->newMockRevisions() ],
		[ new PageIdentifier( $this->newMockTitle(), 123 ), null ],
		];
	}

	private function newMockTitle() {
		return $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newMockRevisions() {
		return $this->getMockBuilder( Revisions::class )
			->disableOriginalConstructor()
			->getMock();
	}

}
