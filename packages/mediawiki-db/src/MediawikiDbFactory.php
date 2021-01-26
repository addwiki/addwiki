<?php

namespace Mediawiki\Db;

use Mediawiki\Db\Service\PageFileExtensionListGetter;
use Mediawiki\Db\Service\RedirectListGetter;
use MediaWiki\Services\LogListGetter;
use MediaWiki\Services\UserGetter;
use PDO;

class MediawikiDbFactory {

	private $db;

	public function __construct( PDO $db ) {
		$this->db = $db;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $fileExtension
	 *
	 * @return PageFileExtensionListGetter
	 */
	public function newPageFileExtensionListGetter( $fileExtension ) {
		return new PageFileExtensionListGetter( $this->db, $fileExtension );
	}

	/**
	 * @since 0.1
	 *
	 * @return LogListGetter
	 */
	public function newLogListGetter() {
		return new DatabaseLogListGetter( $this->db );
	}

	/**
	 * @since 0.1
	 *
	 * @return RedirectListGetter
	 */
	public function newRedirectListGetter() {
		return new RedirectListGetter( $this->db );
	}

	/**
	 * @since 0.1
	 *
	 * @return UserGetter
	 */
	public function newUserGetter() {
		return new DatabaseUserGetter( $this->db );
	}

}
