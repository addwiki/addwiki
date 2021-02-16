<?php

namespace Mediawiki\Sitematrix\Api\Test;

use PHPUnit\Framework\TestCase;
use Mediawiki\Sitematrix\DataModel\Site;
use Mediawiki\Sitematrix\DataModel\SiteList;

/**
 * @author Tarrow
 * @author Addshore
 *
 * @covers Mediawiki\Sitematrix\DataModel\SiteList
 */
class SiteListTest extends TestCase {
	public function testGetSiteArray() {
		$siteArray = [
			new Site( "http://notasite", "adbname", "acode", "aSiteName" ),
			new Site( "http://notasite", "adbname", "acode", "aSiteName" ),
		];
		$actualSiteList = new SiteList( $siteArray );
		$this->assertEquals( $siteArray, $actualSiteList->getSiteArray() );
	}

	public function testGetSiteFromDBName() {
		$siteArray = [
			new Site( "http://notasite", "adbname", "acode", "aSiteName" ),
			new Site( "http://notasite", "adbname-B", "acode", "aSiteName" ),
		];

		$actualSiteList = new SiteList( $siteArray );

		$this->assertEquals(
			new Site( "http://notasite", "adbname", "acode", "aSiteName" ),
			$actualSiteList->getSiteFromDbName( "adbname" )
		);
	}

	public function testGetSiteListForCode() {
		$siteArray = [
			new Site( "http://notasite", "dbname1", "Code1", "aSiteName1" ),
			new Site( "http://notasite", "dbname2", "Code1", "aSiteName2" ),
			new Site( "http://notasite", "dbname3", "Code2", "aSiteName3" ),
		];

		$actualSiteList = new SiteList( $siteArray );

		$this->assertEquals(
			new SiteList( [ $siteArray[0], $siteArray[1] ] ),
			$actualSiteList->getSiteListForCode( "Code1" )
		);
		$this->assertEquals(
			new SiteList( [ $siteArray[2] ] ),
			$actualSiteList->getSiteListForCode( "Code2" )
		);
	}

}
