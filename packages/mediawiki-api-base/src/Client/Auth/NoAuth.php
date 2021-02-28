<?php

namespace Addwiki\Mediawiki\Api\Client\Auth;

/**
 * For use with plain MediaWiki and no authentication (anon)
 */
class NoAuth implements AuthMethod {

	public function preRequestAuth(): void {
		// Nothing is ever needed here
	}

	public function identifierForUserAgent(): ?string {
		return null;
	}

}
