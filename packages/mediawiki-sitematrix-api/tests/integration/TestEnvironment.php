<?php

namespace Addwiki\Mediawiki\Ext\Sitematrix\Test\Integration;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;

class TestEnvironment {

	public static function newDefault(): TestEnvironment {
		return new self();
	}

	private MediawikiFactory $factory;

	public function __construct() {
		$this->factory = new MediawikiFactory( new ActionApi( 'http://localhost/w/api.php' ) );
	}

	public function getFactory(): MediawikiFactory {
		return $this->factory;
	}

}
