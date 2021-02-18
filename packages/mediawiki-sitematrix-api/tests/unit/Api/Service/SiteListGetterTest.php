<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Test\Integration\Api\Service;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use Addwiki\Mediawiki\Ext\Sitematrix\Api\Service\SiteListGetter;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\Site;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\SiteList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mediawiki\Sitematrix\Api\Service\SiteListGetter
 */
class SiteListGetterTest extends TestCase {

	/**
	 * @return MockObject|MediawikiApi
	 */
	private function getMockApi() {
		return $this->getMockBuilder( MediawikiApi::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGetSiteList(): void {
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
			->with( new SimpleRequest( 'sitematrix' ) )
			->willReturn( $siteMatrixArray );

		$service = new SiteListGetter( $mockApi );
		$siteList = $service->getSiteList();

		$this->assertInstanceOf( SiteList::class, $siteList );
		$this->assertCount(
			count( $expectedSites ),
			$siteList->getSiteArray(),
			'Incorrect number of sites returned'
		);
		foreach ( $siteList->getSiteArray() as $site ) {
			$this->assertInstanceOf( Site::class, $site );
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
