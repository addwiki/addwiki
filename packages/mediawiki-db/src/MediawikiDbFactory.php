<?php

namespace Addwiki\Mediawiki\Db;

use Addwiki\Mediawiki\Db\Service\DatabaseLogListGetter;
use Addwiki\Mediawiki\Db\Service\DatabaseUserGetter;
use Addwiki\Mediawiki\Db\Service\PageFileExtensionListGetter;
use Addwiki\Mediawiki\Db\Service\RedirectListGetter;
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
	 * @return DatabaseLogListGetter
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
	 * @return DatabaseUserGetter
	 */
	public function newUserGetter() {
		return new DatabaseUserGetter( $this->db );
	}

}
