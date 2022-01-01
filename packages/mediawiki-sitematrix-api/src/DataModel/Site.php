<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\DataModel;

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

	public function getUrl(): string {
		return $this->url;
	}

	public function getDbName(): string {
		return $this->dbname;
	}

	public function getCode(): string {
		return $this->code;
	}

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
