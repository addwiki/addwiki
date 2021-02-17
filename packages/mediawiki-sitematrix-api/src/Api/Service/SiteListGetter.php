<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Api\Service;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\Site;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\SiteList;

/**
 * @access private
 *
 * @author Addshore
 * @author Tarrow
 */
class SiteListGetter {

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
	 * @return SiteList
	 */
	public function getSiteList() {
		$sitematrixResult = $this->api->getRequest( new SimpleRequest( 'sitematrix' ) );
		unset( $sitematrixResult['sitematrix']['count'] );

		$siteListArray = [];
		foreach ( $sitematrixResult['sitematrix'] as $siteGroup ) {
			foreach ( $siteGroup['site'] as $details ) {
				$siteListArray[] =
					new Site(
						$details['url'],
						$details['dbname'],
						$details['code'],
						$details['sitename']
					);
			}
		}

		return new SiteList( $siteListArray );
	}

}
