<?php

namespace Mediawiki\Sitematrix\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Sitematrix\DataModel\Site;
use Mediawiki\Sitematrix\DataModel\SiteList;

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
		foreach ( $sitematrixResult['sitematrix'] as $key => $siteGroup ) {
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
