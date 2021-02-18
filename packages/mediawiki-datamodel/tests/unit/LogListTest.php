<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Log;
use Addwiki\Mediawiki\DataModel\LogList;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\LogList
 * @author Addshore
 */
class LogListTest extends TestCase {

	public function testJsonRoundTrip(): void {
		$logList = new LogList(
			[
			new Log( 1, 'ty', 'ac', '2014', 'Addshore', new PageIdentifier( null, 22 ), 'comment', [] ),
			new Log( 2, 'ty2', 'ac2', '2015', 'Addbot', new PageIdentifier( null, 33 ), 'comment2', [] ),
			]
		);
		$json = $logList->jsonSerialize();
		$json = json_decode( json_encode( $json ), true );
		$this->assertEquals( $logList, LogList::jsonDeserialize( $json ) );
	}

}
