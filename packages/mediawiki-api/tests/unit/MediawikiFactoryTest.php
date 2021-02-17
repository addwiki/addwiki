<?php

namespace Addwiki\Mediawiki\Api\Client\Tests\Unit;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\MediawikiFactory;
use Addwiki\Mediawiki\Api\Client\Service\FileUploader;
use Addwiki\Mediawiki\Api\Client\Service\ImageRotator;
use Addwiki\Mediawiki\Api\Client\Service\LogListGetter;
use Addwiki\Mediawiki\Api\Client\Service\PageDeleter;
use Addwiki\Mediawiki\Api\Client\Service\PageGetter;
use Addwiki\Mediawiki\Api\Client\Service\PageListGetter;
use Addwiki\Mediawiki\Api\Client\Service\PageMover;
use Addwiki\Mediawiki\Api\Client\Service\PageProtector;
use Addwiki\Mediawiki\Api\Client\Service\PagePurger;
use Addwiki\Mediawiki\Api\Client\Service\PageRestorer;
use Addwiki\Mediawiki\Api\Client\Service\PageWatcher;
use Addwiki\Mediawiki\Api\Client\Service\RevisionDeleter;
use Addwiki\Mediawiki\Api\Client\Service\RevisionPatroller;
use Addwiki\Mediawiki\Api\Client\Service\RevisionRestorer;
use Addwiki\Mediawiki\Api\Client\Service\RevisionRollbacker;
use Addwiki\Mediawiki\Api\Client\Service\RevisionSaver;
use Addwiki\Mediawiki\Api\Client\Service\RevisionUndoer;
use Addwiki\Mediawiki\Api\Client\Service\UserBlocker;
use Addwiki\Mediawiki\Api\Client\Service\UserCreator;
use Addwiki\Mediawiki\Api\Client\Service\UserGetter;
use Addwiki\Mediawiki\Api\Client\Service\UserRightsChanger;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mediawiki\Api\MediawikiFactory
 *
 * @author Addshore
 */
class MediawikiFactoryTest extends TestCase {

	public function getMockMediawikiApi() {
		return $this->getMockBuilder( MediawikiApi::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function provideFactoryMethodsTest() {
		return [
			[ RevisionSaver::class, 'newRevisionSaver' ],
			[ RevisionUndoer::class, 'newRevisionUndoer' ],
			[ PageGetter::class, 'newPageGetter' ],
			[ UserGetter::class, 'newUserGetter' ],
			[ PageDeleter::class, 'newPageDeleter' ],
			[ PageMover::class, 'newPageMover' ],
			[ PageListGetter::class, 'newPageListGetter' ],
			[ PageRestorer::class, 'newPageRestorer' ],
			[ PagePurger::class, 'newPagePurger' ],
			[ RevisionRollbacker::class, 'newRevisionRollbacker' ],
			[ RevisionPatroller::class, 'newRevisionPatroller' ],
			[ PageProtector::class, 'newPageProtector' ],
			[ PageWatcher::class, 'newPageWatcher' ],
			[ RevisionDeleter::class, 'newRevisionDeleter' ],
			[ RevisionRestorer::class, 'newRevisionRestorer' ],
			[ UserBlocker::class, 'newUserBlocker' ],
			[ UserRightsChanger::class, 'newUserRightsChanger' ],
			[ UserCreator::class, 'newUserCreator' ],
			[ LogListGetter::class, 'newLogListGetter' ],
			[ FileUploader::class, 'newFileUploader' ],
			[ ImageRotator::class, 'newImageRotator' ],
		];
	}

	/**
	 * @dataProvider provideFactoryMethodsTest
	 */
	public function testFactoryMethod( $class, $method ) {
		$factory = new MediawikiFactory( $this->getMockMediawikiApi() );
		$this->assertInstanceOf( $class, $factory->$method() );
	}

}
