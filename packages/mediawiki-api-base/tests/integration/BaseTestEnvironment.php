<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\AuthMethod;
use Addwiki\Mediawiki\Api\Client\Auth\OAuthOwnerConsumer;
use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Exception;

class BaseTestEnvironment {

	private string $apiUrl;
	private string $pageUrl;

	/**
	 * Get a new BaseTestEnvironment.
	 * This is identical to calling self::__construct() but is useful for fluent construction.
	 */
	public static function newInstance(): BaseTestEnvironment {
		return new self();
	}

	/**
	 * Set up the test environment by creating a new API object pointing to a
	 * MediaWiki installation on localhost (or elsewhere as specified by the
	 * ADDWIKI_MW_API environment variable).
	 *
	 * @throws Exception If the ADDWIKI_MW_API environment variable does not end in 'api.php'
	 */
	public function __construct() {
		$apiUrl = getenv( 'ADDWIKI_MW_API' );

		if ( !$apiUrl ) {
			$apiUrl = "http://localhost:8877/api.php";
		}

		if ( substr( $apiUrl, -7 ) !== 'api.php' ) {
			$msg = sprintf( 'URL incorrect: %s', $apiUrl )
				. " (Set the ADDWIKI_MW_API environment variable correctly)";
			throw new Exception( $msg );
		}

		$this->apiUrl = $apiUrl;
		$this->pageUrl = str_replace( 'api.php', 'index.php?title=Special:SpecialPages', $apiUrl );
	}

	/**
	 * Get the url of the api to test against, based on the MEDIAWIKI_API_URL environment variable.
	 */
	public function getApiUrl(): string {
		return $this->apiUrl;
	}

	/**
	 * Get the url of a page on the wiki to test against, based on the api url.
	 */
	public function getPageUrl(): string {
		return $this->pageUrl;
	}

	public function getActionApi( ?AuthMethod $auth = null ): ActionApi {
		return new ActionApi( $this->getApiUrl(), $auth );
	}

	public function getUserAndPasswordAuth(): UserAndPassword {
		return new UserAndPassword( 'CIUser', 'LongCIPass123' );
	}

	public function getOAuthOwnerConsumerAuth(): OAuthOwnerConsumer {
		// This file was created and is hosted by the docker-ci setup
		$creationJsonString = file_get_contents( str_replace( 'api.php', 'createOAuthConsumer.json', $this->getApiUrl() ) );
		$data = json_decode( $creationJsonString, true );
		return new OAuthOwnerConsumer( $data['key'], $data['secret'], $data['accessToken'], $data['accessSecret'] );
	}

}
