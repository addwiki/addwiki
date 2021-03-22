<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use Addwiki\Mediawiki\DataModel\Page;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revisions;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Page
 */
class PageTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param null|Revisions&\Addwiki\Mediawiki\DataModel\Tests\Unit\MockObject $revisions
	 */
	public function testValidConstruction( ?PageIdentifier $pageIdentifier, ?Revisions $revisions ): void {
		$page = new Page( $pageIdentifier, $revisions );
		$this->assertEquals( $pageIdentifier, $page->getPageIdentifier() );
		if ( $revisions === null ) {
			$this->assertInstanceOf( Revisions::class, $page->getRevisions() );
		} else {
			$this->assertEquals( $revisions, $page->getRevisions() );
		}
	}

	/**
	 * @return array<int, array<PageIdentifier|Revisions&\Addwiki\Mediawiki\DataModel\Tests\Unit\MockObject|null>>
	 */
	public function provideValidConstruction(): array {
		return [
		[ null, null ],
		[ null, $this->newMockRevisions() ],
		[ new PageIdentifier( $this->newMockTitle(), 1 ), $this->newMockRevisions() ],
		[ new PageIdentifier( $this->newMockTitle(), 123 ), null ],
		];
	}

	/**
	 * @return Title&MockObject
	 */
	private function newMockTitle() {
		return $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @return Revisions&MockObject
	 */
	private function newMockRevisions() {
		return $this->getMockBuilder( Revisions::class )
			->disableOriginalConstructor()
			->getMock();
	}

}
