<?php

namespace Addwiki\Mediawiki\Api\Tests\Integration\Service;

use Addwiki\Mediawiki\Api\Tests\Integration\TestEnvironment;
use PHPUnit\Framework\TestCase;

class UserIntegrationTest extends TestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
	}

	public function testCreateUser(): void {
		$strTime = strval( time() );

		$factory = TestEnvironment::newInstance()->getFactory();
		$createResult = $factory->newUserCreator()->create(
			'TestUser - ' . $strTime,
			$strTime . '-pass'
		);
		$this->assertTrue( $createResult );
	}

}
