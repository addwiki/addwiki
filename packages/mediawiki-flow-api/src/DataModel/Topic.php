<?php

namespace Addwiki\Mediawiki\Ext\Flow\DataModel;

class Topic {

	private $pageName;
	private $header;
	private $content;

	public function __construct( $pageName, $header, $content ) {
		$this->pageName = $pageName;
		$this->header = $header;
		$this->content = $content;
	}

	public function getPageName() {
		return $this->pageName;
	}

	/**
	 * @return mixed
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

}
