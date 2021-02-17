<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Api;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Ext\Sitematrix\Api\Service\SiteListGetter;

/**
 * @access public
 *
 * @author Addshore
 */
class MediawikiSitematrixFactory {

	/**
	 * @var \Addwiki\Mediawiki\Api\Client\MediawikiApi
	 */
	private $api;

	/**
	 * @param \Addwiki\Mediawiki\Api\Client\MediawikiApi $api
	 */
	public function __construct( \Addwiki\Mediawiki\Api\Client\MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * @since 0.1
	 *
	 * @return SiteListGetter
	 */
	public function newSiteListGetter() {
		return new SiteListGetter( $this->api );
	}

}
