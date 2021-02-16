<?php

namespace Mediawiki\Sitematrix\Api\Test;

use Mediawiki\Sitematrix\DataModel\Site;
use PHPUnit\Framework\TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Sitematrix\DataModel\Site
 */
class SiteTest extends TestCase {

	public function testEverything() {
		$site = new Site( 'a', 'b', 'c', 'd', [ 'z', 'x' ] );
		$this->assertEquals( 'a', $site->getUrl() );
		$this->assertEquals( 'b', $site->getDbName() );
		$this->assertEquals( 'c', $site->getCode() );
		$this->assertEquals( 'd', $site->getSiteName() );
		$this->assertEquals( [ 'z', 'x' ], $site->getFlags() );
	}

}
