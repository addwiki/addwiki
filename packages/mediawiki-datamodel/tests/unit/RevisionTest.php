<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Content;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revision;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Revision
 * @author Addshore
 */
class RevisionTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param Content&MockObject[]|null[] $content
	 * @param Content&MockObject[]|PageIdentifier[]|null[] $pageIdentifier
	 * @param int[]|Content&MockObject[]|PageIdentifier[]|null[] $id
	 * @param int[]|Content&MockObject[]|EditInfo&MockObject[]|PageIdentifier[]|null[] $editInfo
	 * @param int[]|string[]|Content&MockObject[]|EditInfo&MockObject[]|PageIdentifier[]|null[] $user
	 * @param int[]|string[]|Content&MockObject[]|EditInfo&MockObject[]|PageIdentifier[] $timestamp
	 */
	public function testValidConstruction( array $content, array $pageIdentifier, array $id, array $editInfo, array $user, array $timestamp ): void {
		$rev = new Revision( $content, $pageIdentifier, $id, $editInfo, $user, $timestamp );
		$this->assertEquals( $content, $rev->getContent() );
		if ( $pageIdentifier !== null ) {
			$this->assertEquals( $pageIdentifier, $rev->getPageIdentifier() );
		} else {
			$this->assertInstanceOf( PageIdentifier::class, $rev->getPageIdentifier() );
		}

		$this->assertEquals( $id, $rev->getId() );
		if ( $editInfo !== null ) {
			$this->assertEquals( $editInfo, $rev->getEditInfo() );
		} else {
			$this->assertInstanceOf( EditInfo::class, $rev->getEditInfo() );
		}
		$this->assertEquals( $user, $rev->getUser() );
		$this->assertEquals( $timestamp, $rev->getTimestamp() );
	}

	public function provideValidConstruction(): array {
		$mockContent = $this->getMockBuilder( Content::class )
			->disableOriginalConstructor()
			->getMock();
		$mockEditInfo = $this->getMockBuilder( EditInfo::class )
			->disableOriginalConstructor()
			->getMock();
		$mockTitle = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		return [
		[ $mockContent, null, null, null, null, null ],
		[ $mockContent, new PageIdentifier( null, 1 ), null , null, null,null ],
		[ $mockContent, new PageIdentifier( null, 1 ), 1 , null, null, null ],
		[ $mockContent, new PageIdentifier( null, 2 ), 1 , $mockEditInfo, null, null ],
		[ $mockContent, new PageIdentifier( $mockTitle ), 1 , $mockEditInfo, 'foo', null ],
		[ $mockContent, new PageIdentifier( $mockTitle, 3 ), 1 , $mockEditInfo, 'foo', '20141212121212' ],
		];
	}

}
