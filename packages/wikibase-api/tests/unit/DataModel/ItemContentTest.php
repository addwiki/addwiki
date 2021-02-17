<?php

namespace Addwiki\Wikibase\Api\Tests\Unit\DataModel;

use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;
use Addwiki\Wikibase\DataModel\ItemContent;

/**
 * @covers Wikibase\DataModel\ItemContent
 * @author Addshore
 */
class ItemContentTest extends TestCase {

	public function testModel() {
		$this->assertEquals( 'wikibase-item', ItemContent::MODEL );
	}

	public function testValidConstruction() {
		$item = new Item();
		$content = new ItemContent( $item );
		$this->assertEquals( $item, $content->getData() );
		$this->assertIsString( $content->getHash() );
	}

}
