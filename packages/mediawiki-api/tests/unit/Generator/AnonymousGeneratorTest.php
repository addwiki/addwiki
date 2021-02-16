<?php

namespace Mediawiki\Api\Test\Generator;

use PHPUnit\Framework\TestCase;
use Mediawiki\Api\Generator\AnonymousGenerator;

/**
 * @author Addshore
 *
 * @covers \Mediawiki\Api\Generator\AnonymousGenerator
 */
class AnonymousGeneratorTest extends TestCase {

	public function testConstruction() {
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
