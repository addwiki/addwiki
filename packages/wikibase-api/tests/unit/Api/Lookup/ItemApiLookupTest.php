<?php

namespace Wikibase\Api\Lookup\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Wikibase\Api\Lookup\ItemApiLookup;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\EntityLookup;

/**
 * @author Addshore
 *
 * @covers Wikibase\Api\Lookup\ItemApiLookup
 */
class ItemApiLookupTest extends TestCase {

	public function testGetItemForId() {
		/** @var EntityLookup|PHPUnit_Framework_MockObject_MockObject $lookupMock */
		$lookupMock = $this->createMock( EntityLookup::class );
		$lookupMock->expects( $this->once() )
			->method( 'getEntity' )
			->with( $this->equalTo( new ItemId( 'Q42' ) ) )
			->will( $this->returnValue( new Item( new ItemId( 'Q42' ) ) ) );

		$itemApiLookup = new ItemApiLookup( $lookupMock );
		$this->assertEquals(
			new Item( new ItemId( 'Q42' ) ),
			$itemApiLookup->getItemForId( new ItemId( 'Q42' ) )
		);
	}

	public function testGetItemForIdWithException() {
		/** @var EntityLookup|PHPUnit_Framework_MockObject_MockObject $lookupMock */
		$lookupMock = $this->createMock( EntityLookup::class );
		$lookupMock->expects( $this->once() )
			->method( 'getEntity' )
			->with( $this->equalTo( new ItemId( 'Q42' ) ) )
			->will( $this->returnValue( null ) );

		$itemApiLookup = new ItemApiLookup( $lookupMock );

		$result = $itemApiLookup->getItemForId( new ItemId( 'Q42' ) );
		$this->assertNull( $result );
	}
}
