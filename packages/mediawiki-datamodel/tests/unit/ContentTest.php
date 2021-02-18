<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Content;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContentTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param string[]|null[] $data
	 * @param string[]|null[] $model
	 */
	public function testValidConstruction( array $data, array $model ): void {
		$content = new Content( $data, $model );
		$this->assertEquals( $data, $content->getData() );
		$this->assertEquals( $model, $content->getModel() );
		$this->assertTrue( is_string( $content->getHash() ) );
		$this->assertFalse( $content->hasChanged() );
	}

	/**
	 * @return string[][]|stdClass[][]|null[][]
	 */
	public function provideValidConstruction(): array {
		return [
		[ '', null ],
		[ 'foo', null ],
		[ new stdClass(), null ],
		];
	}

}
