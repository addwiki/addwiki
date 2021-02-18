<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

class File extends Page {

	private string $url;

	public function __construct( string $url, PageIdentifier $pageIdentifier = null, Revisions $revisions = null ) {
		parent::__construct( $pageIdentifier, $revisions );
		if ( !is_string( $url ) ) {
			throw new InvalidArgumentException( '$url must be a string' );
		}
		$this->url = $url;
	}

	public function getUrl(): string {
		return $this->url;
	}

}
