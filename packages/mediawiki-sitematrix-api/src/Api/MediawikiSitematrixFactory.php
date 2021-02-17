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
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
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
