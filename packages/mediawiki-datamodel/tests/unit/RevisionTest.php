<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\Revision
 * @author Addshore
 */
class RevisionTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $content, $pageIdentifier, $id, $editInfo, $user, $timestamp ) {
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

	public function provideValidConstruction() {
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
