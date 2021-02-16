<?php

namespace Mediawiki\DataModel\Test;

use PHPUnit\Framework\TestCase;
use stdClass;
use Mediawiki\DataModel\Content;

class ContentTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $data, $model ) {
		$content = new Content( $data, $model );
		$this->assertEquals( $data, $content->getData() );
		$this->assertEquals( $model, $content->getModel() );
		$this->assertTrue( is_string( $content->getHash() ) );
		$this->assertFalse( $content->hasChanged() );
	}

	public function provideValidConstruction() {
		return [
		[ '', null ],
		[ 'foo', null ],
		[ new stdClass(), null ],
		];
	}

}
