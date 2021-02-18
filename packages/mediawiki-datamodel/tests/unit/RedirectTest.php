<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\Redirect;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\Redirect
 * @author Addshore
 */
class RedirectTest extends TestCase {

	public function testJsonRoundTrip(): void {
		$title = new Redirect( new Title( 'Foo', 12 ), new Title( 'bar', 13 ) );
		$json = $title->jsonSerialize();
		$this->assertEquals( $title, Redirect::jsonDeserialize( $json ) );
	}

}
