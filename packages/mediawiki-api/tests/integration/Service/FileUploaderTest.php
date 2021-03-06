<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration\Service;

use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\Api\Service\FileUploader;
use Addwiki\Mediawiki\Api\Tests\Integration\TestEnvironment;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * Test the \Addwiki\Addwiki\Mediawiki\Api\Service\FileUploader class.
 */
class FileUploaderTest extends TestCase {

	protected ?MediawikiFactory $factory = null;

	protected ?FileUploader $fileUploader = null;

	/**
	 * Create a FileUploader to use in all these tests.
	 */
	protected function setup(): void {
		parent::setup();
		$testEnvironment = TestEnvironment::newInstance();
		$this->factory = new MediawikiFactory( $testEnvironment->getApiAuthed() );
		$this->fileUploader = $this->factory->newFileUploader();
	}

	public function testUpload(): void {
		$testPagename = uniqid( 'file-uploader-test-' ) . '.png';
		$testTitle = new Title( 'File:' . $testPagename );

		// Check that the file doesn't exist yet.
		$testFile = $this->factory->newPageGetter()->getFromTitle( $testTitle );
		$this->assertSame( 0, $testFile->getPageIdentifier()->getId() );

		// Upload a file.
		$testFilename = dirname( __DIR__, 2 ) . '/fixtures/blue ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ.png';
		$uploaded = $this->fileUploader->upload( $testPagename, $testFilename, 'Testing',
			'', null, true );
		$this->assertTrue( $uploaded );

		// Get the file again, and check that it exists this time.
		$testFile2 = $this->factory->newPageGetter()->getFromTitle( $testTitle );
		$this->assertGreaterThan( 0, $testFile2->getPageIdentifier()->getId() );
	}

	public function testUploadByChunks(): void {
		$testPagename = uniqid( 'file-uploader-test-' ) . '.png';
		$testTitle = new Title( 'File:' . $testPagename );

		// Upload a 83725 byte file in 10k chunks.
		$testFilename = dirname( __DIR__, 2 ) . '/fixtures/blue ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ.png';
		$this->fileUploader->setChunkSize( 1024 * 10 );
		$uploaded = $this->fileUploader->upload( $testPagename, $testFilename, 'Testing',
			null, null, true );
		$this->assertTrue( $uploaded );

		// Get the file again, and check that it exists this time.
		$testFile2 = $this->factory->newPageGetter()->getFromTitle( $testTitle );
		$this->assertGreaterThan( 0, $testFile2->getPageIdentifier()->getId() );
	}

}
