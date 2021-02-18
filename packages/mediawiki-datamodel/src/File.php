<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * @author Addshore
 */
class File extends Page {

	private string $url;

	/**
	 * @param PageIdentifier|null $pageIdentifier
	 * @param Revisions|null $revisions
	 */
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
