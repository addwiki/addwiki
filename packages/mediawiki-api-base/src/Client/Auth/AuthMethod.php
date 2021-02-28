<?php

namespace Addwiki\Mediawiki\Api\Client\Auth;

interface AuthMethod {

	/**
	 * This will be called before every request to the API.
	 * It is up to the implementations to decide if anything needs to be done here, such as a call to action=login
	 */
	public function preRequestAuth(): void;

	/**
	 * We want to provide a useful user agent, not matter the authentication method.
	 * So allow the method to define what is provided.
	 * This could be a username, or a consumer ID for example.
	 * null can be used if the method can't provide anything useful.
	 *
	 * Example: "user/Addshore" or "oauth-consumer/123abc"
	 */
	public function identifierForUserAgent(): ?string;

}
