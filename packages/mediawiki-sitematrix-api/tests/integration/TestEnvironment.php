<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Test\Integration;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\MediawikiFactory;

/**
 * @author Addshore
 */
class TestEnvironment {

	public static function newDefault() {
		return new self();
	}

	private $factory;

	public function __construct() {
		$this->factory = new MediawikiFactory( new MediawikiApi( 'http://localhost/w/api.php' ) );
	}

	public function getFactory() {
		return $this->factory;
	}

}
