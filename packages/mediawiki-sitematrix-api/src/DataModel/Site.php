<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\DataModel;

/**
 * @since 0.1
 *
 * @author Addshore
 */
class Site {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $dbname;

	/**
	 * @var string
	 */
	private $code;

	/**
	 * @var string
	 */
	private $sitename;

	/**
	 * @var string[]
	 */
	private $flags = [];

	/**
	 * @param string $url
	 * @param string $dbname
	 * @param string $code
	 * @param string $sitename
	 * @param string[] $flags
	 */
	public function __construct( $url, $dbname, $code, $sitename, array $flags = [] ) {
		$this->url = $url;
		$this->dbname = $dbname;
		$this->code = $code;
		$this->sitename = $sitename;
		$this->flags = $flags;
	}

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getDbName() {
		return $this->dbname;
	}

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getSiteName() {
		return $this->sitename;
	}

	/**
	 * @return string[]
	 */
	public function getFlags() {
		return $this->flags;
	}

}
