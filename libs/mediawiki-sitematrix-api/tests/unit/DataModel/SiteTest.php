<?php

namespace Mediawiki\Sitematrix\Api\Test;

use Mediawiki\Sitematrix\DataModel\Site;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Sitematrix\DataModel\Site
 */
class SiteTest extends PHPUnit_Framework_TestCase {

	public function testEverything() {
		$site = new Site( 'a', 'b', 'c', 'd', array( 'z', 'x' ) );
		$this->assertEquals( 'a', $site->getUrl() );
		$this->assertEquals( 'b', $site->getDbName() );
		$this->assertEquals( 'c', $site->getCode() );
		$this->assertEquals( 'd', $site->getSiteName() );
		$this->assertEquals( array( 'z', 'x'), $site->getFlags() );
	}

}
