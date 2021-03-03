<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Api\Service;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\Site;
use Addwiki\Mediawiki\Ext\Sitematrix\DataModel\SiteList;

/**
 * @access private
 */
class SiteListGetter {

	private ActionApi $api;

	public function __construct( ActionApi $api ) {
		$this->api = $api;
	}

	public function getSiteList(): SiteList {
		$sitematrixResult = $this->api->request( ActionRequest::simpleGet( 'sitematrix' ) );
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
