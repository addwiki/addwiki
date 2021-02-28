<?php

namespace Addwiki\Mediawiki\Api\Client\Auth;

use InvalidArgumentException;

/**
 * For use with https://www.mediawiki.org/wiki/Extension:Oauth
 * https://www.mediawiki.org/wiki/OAuth/Owner-only_consumers
 */
class OAuthOwnerConsumer implements AuthMethod {

	private string $consumerKey;
	private string $consumerSecret;
	private string $accessToken;
	private string $accessSecret;

	public function __construct( string $consumerKey, string $consumerSecret, string $accessToken, string $accessSecret ) {
		if ( empty( $consumerKey ) || empty( $consumerSecret ) || empty( $accessToken ) || empty( $accessSecret ) ) {
			throw new InvalidArgumentException( 'No empty fields allowed' );
		}
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->accessToken = $accessToken;
		$this->accessSecret = $accessSecret;
	}

	public function getConsumerKey(): string {
		return $this->consumerKey;
	}

	public function getConsumeSecret(): string {
		return $this->consumerSecret;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function getAccessSecret(): string {
		return $this->accessSecret;
	}

	public function equals( OAuthOwnerConsumer $other ): bool {
		return $this->getConsumerKey() === $other->getConsumerKey()
			&& $this->getConsumeSecret() === $other->getConsumeSecret()
			&& $this->getAccessToken() === $other->getAccessToken()
			&& $this->getAccessSecret() === $other->getAccessSecret();
	}

}
