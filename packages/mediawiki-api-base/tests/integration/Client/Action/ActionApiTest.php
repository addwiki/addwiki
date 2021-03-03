<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration\Client\Action;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Action\Request\SimpleRequest;
use Addwiki\Mediawiki\Api\Client\RsdException;
use Addwiki\Mediawiki\Api\Tests\Integration\BaseTestEnvironment;
use PHPUnit\Framework\TestCase;

class ActionApiTest extends TestCase {

	public function testNewFromPage(): void {
		$api = ActionApi::newFromPage( BaseTestEnvironment::newInstance()->getPageUrl() );
		$this->assertInstanceOf( ActionApi::class, $api );
	}

	public function testNewFromPageInvalidHtml(): void {
		$this->expectException( RsdException::class );
		$this->expectExceptionMessageMatches( "/Unable to find RSD URL in page.*/" );
		// This could be any URL that doesn't contain the RSD link, load.php works just fine!
		$nonWikiPage = str_replace( 'api.php', 'load.php', BaseTestEnvironment::newInstance()->getApiUrl() );
		ActionApi::newFromPage( $nonWikiPage );
	}

	/**
	 * Duplicate element IDs break DOMDocument::loadHTML
	 * @see https://phabricator.wikimedia.org/T163527#3219833
	 */
	public function testNewFromPageWithDuplicateId(): void {
		$testPageName = __METHOD__;
		$testEnv = BaseTestEnvironment::newInstance();
		$wikiPageUrl = str_replace( 'api.php', sprintf( 'index.php?title=%s', $testPageName ), $testEnv->getApiUrl() );
		$api = $testEnv->getApi();

		// Test with no duplicate IDs.
		$this->savePage( $api, $testPageName, '<p id="unique-id"></p>' );
		$api1 = ActionApi::newFromPage( $wikiPageUrl );
		$this->assertInstanceOf( ActionApi::class, $api1 );

		// Test with duplicate ID.
		$wikiText = '<p id="duplicated-id"></p><div id="duplicated-id"></div>';
		$this->savePage( $api, $testPageName, $wikiText );
		$api2 = ActionApi::newFromPage( $wikiPageUrl );
		$this->assertInstanceOf( ActionApi::class, $api2 );
	}

	private function savePage( ActionApi $api, string $title, string $content ): void {
		$params = [
			'title' => $title,
			'text' => $content,
			'md5' => md5( $content ),
			'token' => $api->getToken(),
		];
		$api->postRequest( new SimpleRequest( 'edit', $params ) );
	}

	public function testQueryGetResponse(): void {
		$api = BaseTestEnvironment::newInstance()->getApi();
		$response = $api->getRequest( new SimpleRequest( 'query' ) );
		$this->assertIsArray( $response );
	}

	public function testQueryGetResponseAsync(): void {
		$api = BaseTestEnvironment::newInstance()->getApi();
		$response = $api->getRequestAsync( new SimpleRequest( 'query' ) );
		$this->assertIsArray( $response->wait() );
	}

	public function testQueryPostResponse(): void {
		$api = BaseTestEnvironment::newInstance()->getApi();
		$response = $api->postRequest( new SimpleRequest( 'query' ) );
		$this->assertIsArray( $response );
	}

	public function testQueryPostResponseAsync(): void {
		$api = BaseTestEnvironment::newInstance()->getApi();
		$response = $api->postRequestAsync( new SimpleRequest( 'query' ) );
		$this->assertIsArray( $response->wait() );
	}

}
