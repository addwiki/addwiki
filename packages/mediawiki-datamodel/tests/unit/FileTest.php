<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\File;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revisions;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\File
 */
class FileTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( string $url ): void {
		$file = new File(
			$url,
			new PageIdentifier( $this->newMockTitle(), 1 ),
			$this->newMockRevisions()
		);
		$this->assertEquals( $url, $file->getUrl() );
	}

	public function provideValidConstruction(): array {
		return [
		[ 'http://upload.wikimedia.org/wikipedia/en/3/39/Journal_of_Geek_Studies_-_logo.jpg' ],
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
