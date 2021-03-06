<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Generator;

use Addwiki\Mediawiki\Api\Generator\AnonymousGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\Api\Generator\AnonymousGenerator
 */
class AnonymousGeneratorTest extends TestCase {

	public function testConstruction(): void {
		$generator = new AnonymousGenerator( 'name', [ 'gfoo' => 'bar' ] );

		$this->assertEquals(
			[
				'generator' => 'name',
				'gfoo' => 'bar',
			],
			$generator->getParams()
		);
	}

}
