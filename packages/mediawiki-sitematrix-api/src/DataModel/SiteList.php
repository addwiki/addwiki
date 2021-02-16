<?php

namespace Mediawiki\Sitematrix\DataModel;

/**
 * @since 0.1
 *
 * @author Addshore
 * @author Tarrow
 */
class SiteList {

	/**
	 * @var Site[]
	 */
	private $sites;

	/**
	 * @param Site[] $sites
	 */
	public function __construct( $sites ) {
		$this->sites = $sites;
	}

	/**
	 * @return Site[]
	 */
	public function getSiteArray() {
		return $this->sites;
	}

	/**
	 * @param string $dbName
	 *
	 * @return Site|null
	 */
	public function getSiteFromDbName( $dbName ) {
		foreach ( $this->sites as $site ) {
			if ( $site->getDbName() === $dbName ) {
				return $site;
			}
		}

		return null;
	}

	/**
	 * @param string $code
	 *
	 * @return SiteList
	 */
	public function getSiteListForCode( $code ) {
		$siteList = [];
		foreach ( $this->sites as $site ) {
			if ( $site->getCode() === $code ) {
				$siteList[] = $site;
			}
		}

		return new SiteList( $siteList );
	}

	/**
	 * @param string $flag
	 *
	 * @return SiteList
	 */
	public function getSiteListForFlag( $flag ) {
		// TODO implement me
	}

}
