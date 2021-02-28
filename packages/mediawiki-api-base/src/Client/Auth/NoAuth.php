<?php

namespace Addwiki\Mediawiki\Api\Client\Auth;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\Request;

/**
 * For use with plain MediaWiki and no authentication (anon)
 */
class NoAuth implements AuthMethod {

	public function preRequestAuth( Request $request, MediawikiApi $api ): void {
		// Nothing is ever needed here
	}

	public function identifierForUserAgent(): ?string {
		return null;
	}

}
