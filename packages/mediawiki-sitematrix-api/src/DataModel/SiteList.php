<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\DataModel;

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
	private array $sites = [];

	/**
	 * @param Site[] $sites
	 */
	public function __construct( array $sites ) {
		$this->sites = $sites;
	}

	/**
	 * @return Site[]
	 */
	public function getSiteArray(): array {
		return $this->sites;
	}

	public function getSiteFromDbName( string $dbName ): ?Site {
		foreach ( $this->sites as $site ) {
			if ( $site->getDbName() === $dbName ) {
				return $site;
			}
		}

		return null;
	}

	public function getSiteListForCode( string $code ): SiteList {
		$siteList = [];
		foreach ( $this->sites as $site ) {
			if ( $site->getCode() === $code ) {
				$siteList[] = $site;
			}
		}

		return new SiteList( $siteList );
	}

}
