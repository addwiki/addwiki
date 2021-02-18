<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Test\Integration;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;

/**
 * @author Addshore
 */
class TestEnvironment {

	public static function newDefault(): \Addwiki\Mediawiki\Ext\Sitematrix\Test\Integration\TestEnvironment {
		return new self();
	}

	private MediawikiFactory $factory;

	public function __construct() {
		$this->factory = new MediawikiFactory( new MediawikiApi( 'http://localhost/w/api.php' ) );
	}

	public function getFactory(): MediawikiFactory {
		return $this->factory;
	}

}
