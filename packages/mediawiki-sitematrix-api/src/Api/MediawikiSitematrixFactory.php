<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Api;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Ext\Sitematrix\Api\Service\SiteListGetter;

/**
 * @access public
 */
class MediawikiSitematrixFactory {

	private MediawikiApi $api;

	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	public function newSiteListGetter(): SiteListGetter {
		return new SiteListGetter( $this->api );
	}

}
