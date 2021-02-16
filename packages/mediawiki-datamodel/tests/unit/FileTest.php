<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\File;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revisions;
use Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\File
 * @author Addshore
 */
class FileTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $url ) {
		$file = new File(
			$url,
			new PageIdentifier( $this->newMockTitle(), 1 ),
			$this->newMockRevisions()
		);
		$this->assertEquals( $url, $file->getUrl() );
	}

	public function provideValidConstruction() {
		return [
		[ 'http://upload.wikimedia.org/wikipedia/en/3/39/Journal_of_Geek_Studies_-_logo.jpg' ],
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
