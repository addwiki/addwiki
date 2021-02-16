<?php

namespace Mediawiki\DataModel\Test;

use InvalidArgumentException;
use Mediawiki\DataModel\EditInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\EditInfo
 * @author Addshore
 */
class EditInfoTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $sum, $minor, $bot ) {
		$flags = new EditInfo( $sum, $minor, $bot );
		$this->assertEquals( $sum, $flags->getSummary() );
		$this->assertEquals( $minor, $flags->getMinor() );
		$this->assertEquals( $bot, $flags->getBot() );
	}

	public function provideValidConstruction() {
		return [
		[ '', EditInfo::MINOR, EditInfo::BOT ],
		[ '', EditInfo::MINOR, EditInfo::NOTBOT ],
		[ '', EditInfo::NOTMINOR, EditInfo::BOT ],
		[ '', EditInfo::NOTMINOR, EditInfo::NOTBOT ],
		[ 'FOO', EditInfo::NOTMINOR, EditInfo::NOTBOT ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $sum, $minor, $bot ) {
		$this->expectException( InvalidArgumentException::class );
		new EditInfo( $sum, $minor, $bot );
	}

	public function provideInvalidConstruction() {
		return [
		[ 1, 2, 3 ],
		[ "foo", false, 3 ],
		[ "foo", 3, false ],
		[ [], true, false ],
		];
	}

}
