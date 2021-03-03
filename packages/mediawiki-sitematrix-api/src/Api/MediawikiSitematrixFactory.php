<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Api;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Ext\Sitematrix\Api\Service\SiteListGetter;

/**
 * @access public
 */
class MediawikiSitematrixFactory {

	private ActionApi $api;

	public function __construct( ActionApi $api ) {
		$this->api = $api;
	}

	public function newSiteListGetter(): SiteListGetter {
		return new SiteListGetter( $this->api );
	}

}
