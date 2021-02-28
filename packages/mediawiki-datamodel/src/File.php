<?php

namespace Addwiki\Mediawiki\DataModel;

class File extends Page {

	private string $url;

	public function __construct( string $url, PageIdentifier $pageIdentifier = null, Revisions $revisions = null ) {
		parent::__construct( $pageIdentifier, $revisions );
		$this->url = $url;
	}

	public function getUrl(): string {
		return $this->url;
	}

}
