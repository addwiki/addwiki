<?php

namespace Mediawiki\Sitematrix\Api\Test;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Sitematrix\Api\Service\SiteListGetter;

/**
 * @author Addshore
 * @author Tarrow
 *
 * @covers Mediawiki\Sitematrix\Api\Service\SiteListGetter
 */
class SiteListGetterTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|MediawikiApi
	 */
	private function getMockApi() {
		return $this->getMockBuilder( '\Mediawiki\Api\MediawikiApi' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGetSiteList() {
		$mockApi = $this->getMockApi();

		$siteMatrixArray = [
			'sitematrix' => [
				'count' => 4,
				[
					'code' => 'en',
					'name' => 'English',
					'site' => [
						[
							'url' => 'https://en.wikipedia.org',
							'dbname' => 'enwiki',
							'code' => 'wiki',
							'sitename' => 'Wikipedia',
						],
						[
							'url' => 'https://en.wiktionary.org',
							'dbname' => 'enwiktionary',
							'code' => 'wiktionary',
							'sitename' => 'Wiktionary',
						],
					],
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
					'site' => [
						[
							'url' => 'https://de.wikipedia.org',
							'dbname' => 'dewiki',
							'code' => 'wiki',
							'sitename' => 'Wikipedia',
						],
						[
							'url' => 'https://de.wikibooks.org',
							'dbname' => 'dewikibooks',
							'code' => 'wikibooks',
							'sitename' => 'Wikibooks',
						],
					],
				],
			],
		];

		$expectedSites = [
			'enwiki' => [
				'url' => 'https://en.wikipedia.org',
				'dbname' => 'enwiki',
				'code' => 'wiki',
				'sitename' => 'Wikipedia',
			],
			'enwiktionary' => [
				'url' => 'https://en.wiktionary.org',
				'dbname' => 'enwiktionary',
				'code' => 'wiktionary',
				'sitename' => 'Wiktionary',
			],
			'dewiki' => [
				'url' => 'https://de.wikipedia.org',
				'dbname' => 'dewiki',
				'code' => 'wiki',
				'sitename' => 'Wikipedia',
			],
			'dewikibooks' => [
				'url' => 'https://de.wikibooks.org',
				'dbname' => 'dewikibooks',
				'code' => 'wikibooks',
				'sitename' => 'Wikibooks',
			],
		];

		$mockApi->expects( $this->once() )
			->method( 'getRequest' )
			->with( $this->equalTo( new SimpleRequest( 'sitematrix' ) ) )
			->will( $this->returnValue( $siteMatrixArray ) );

		$service = new SiteListGetter( $mockApi );
		$siteList = $service->getSiteList();

		$this->assertInstanceOf( 'Mediawiki\Sitematrix\DataModel\SiteList', $siteList );
		$this->assertCount(
			count( $expectedSites ),
			$siteList->getSiteArray(),
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
