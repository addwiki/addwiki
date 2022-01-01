<?php

namespace Addwiki\Mediawiki\Ext\Flow\DataModel;

class Topic {

	private string $pageName;

	private string $header;

	private string $content;

	public function __construct( string $pageName, string $header, string $content ) {
		$this->pageName = $pageName;
		$this->header = $header;
		$this->content = $content;
	}

	public function getPageName(): string {
		return $this->pageName;
	}

	public function getHeader(): string {
		return $this->header;
	}

	public function getContent(): string {
		return $this->content;
	}

}
