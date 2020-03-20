<?php

namespace Mediawiki\Sitematrix\Api\Test;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Sitematrix\Api\Service\SiteListGetter;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 * @author Tarrow
 *
 * @covers Mediawiki\Sitematrix\Api\Service\SiteListGetter
 */
class SiteListGetterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|MediawikiApi
	 */
	private function getMockApi() {
		$mock = $this->getMockBuilder( '\Mediawiki\Api\MediawikiApi' )
			->disableOriginalConstructor()
			->getMock();

		return $mock;
	}

	public function testGetSiteList() {
		$mockApi = $this->getMockApi();

		$siteMatrixArray = array(
			'sitematrix' => array(
				'count' => 4,
				array(
					'code' => 'en',
					'name' => 'English',
					'site' => array(
						array(
							'url' => 'https://en.wikipedia.org',
							'dbname' => 'enwiki',
							'code' => 'wiki',
							'sitename' => 'Wikipedia',
						),
						array(
							'url' => 'https://en.wiktionary.org',
							'dbname' => 'enwiktionary',
							'code' => 'wiktionary',
							'sitename' => 'Wiktionary',
						),
					),
				),
				array(
					'code' => 'de',
					'name' => 'Deutsch',
					'site' => array(
						array(
							'url' => 'https://de.wikipedia.org',
							'dbname' => 'dewiki',
							'code' => 'wiki',
							'sitename' => 'Wikipedia',
						),
						array(
							'url' => 'https://de.wikibooks.org',
							'dbname' => 'dewikibooks',
							'code' => 'wikibooks',
							'sitename' => 'Wikibooks',
						),
					),
				),
			),
		);

		$expectedSites = array(
			'enwiki' => array(
				'url' => 'https://en.wikipedia.org',
				'dbname' => 'enwiki',
				'code' => 'wiki',
				'sitename' => 'Wikipedia',
			),
			'enwiktionary' => array(
				'url' => 'https://en.wiktionary.org',
				'dbname' => 'enwiktionary',
				'code' => 'wiktionary',
				'sitename' => 'Wiktionary',
			),
			'dewiki' => array(
				'url' => 'https://de.wikipedia.org',
				'dbname' => 'dewiki',
				'code' => 'wiki',
				'sitename' => 'Wikipedia',
			),
			'dewikibooks' => array(
				'url' => 'https://de.wikibooks.org',
				'dbname' => 'dewikibooks',
				'code' => 'wikibooks',
				'sitename' => 'Wikibooks',
			),
		);

		$mockApi->expects( $this->once() )
			->method( 'getRequest' )
			->with( $this->equalTo( new SimpleRequest( 'sitematrix' ) ) )
			->will( $this->returnValue( $siteMatrixArray ) );

		$service = new SiteListGetter( $mockApi );
		$siteList = $service->getSiteList();

		$this->assertInstanceOf( 'Mediawiki\Sitematrix\DataModel\SiteList', $siteList );
		$this->assertEquals(
			count( $expectedSites ),
			count( $siteList->getSiteArray() ),
			'Incorrect number of sites returned'
		);
		foreach ( $siteList->getSiteArray() as $site ) {
			$this->assertInstanceOf( 'Mediawiki\Sitematrix\DataModel\Site', $site );
			$this->assertArrayHasKey( $site->getDbName(), $expectedSites );
			$this->assertEquals( $expectedSites[$site->getDbName()]['url'], $site->getUrl() );
			$this->assertEquals( $expectedSites[$site->getDbName()]['dbname'], $site->getDbName() );
			$this->assertEquals( $expectedSites[$site->getDbName()]['code'], $site->getCode() );
			$this->assertEquals(
				$expectedSites[$site->getDbName()]['sitename'],
				$site->getSiteName()
			);
		}
	}

}
