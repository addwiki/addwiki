<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\DataModel;

/**
 * @since 0.1
 *
 * @author Addshore
 */
class Site {

	private string $url;

	private string $dbname;

	private string $code;

	private string $sitename;

	/**
	 * @var string[]
	 */
	private array $flags = [];

	/**
	 * @param string[] $flags
	 */
	public function __construct( string $url, string $dbname, string $code, string $sitename, array $flags = [] ) {
		$this->url = $url;
		$this->dbname = $dbname;
		$this->code = $code;
		$this->sitename = $sitename;
		$this->flags = $flags;
	}

	/**
	 * @since 0.1
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @since 0.1
	 */
	public function getDbName(): string {
		return $this->dbname;
	}

	/**
	 * @since 0.1
	 */
	public function getCode(): string {
		return $this->code;
	}

	/**
	 * @since 0.1
	 */
	public function getSiteName(): string {
		return $this->sitename;
	}

	/**
	 * @return string[]
	 */
	public function getFlags(): array {
		return $this->flags;
	}

}
