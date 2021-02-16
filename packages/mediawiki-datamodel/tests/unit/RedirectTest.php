<?php

namespace Mediawiki\DataModel\Test;

use PHPUnit\Framework\TestCase;
use Mediawiki\DataModel\Redirect;
use Mediawiki\DataModel\Title;

/**
 * @covers \Mediawiki\DataModel\Redirect
 * @author Addshore
 */
class RedirectTest extends TestCase {

	public function testJsonRoundTrip() {
		$title = new Redirect( new Title( 'Foo', 12 ), new Title( 'bar', 13 ) );
		$json = $title->jsonSerialize();
		$this->assertEquals( $title, Redirect::jsonDeserialize( $json ) );
	}

}
