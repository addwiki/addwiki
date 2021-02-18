<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\EditInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\EditInfo
 */
class EditInfoTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( string $sum, bool $minor, bool $bot ): void {
		$flags = new EditInfo( $sum, $minor, $bot );
		$this->assertEquals( $sum, $flags->getSummary() );
		$this->assertEquals( $minor, $flags->getMinor() );
		$this->assertEquals( $bot, $flags->getBot() );
	}

	public function provideValidConstruction(): array {
		return [
		[ '', EditInfo::MINOR, EditInfo::BOT ],
		[ '', EditInfo::MINOR, EditInfo::NOTBOT ],
		[ '', EditInfo::NOTMINOR, EditInfo::BOT ],
		[ '', EditInfo::NOTMINOR, EditInfo::NOTBOT ],
		[ 'FOO', EditInfo::NOTMINOR, EditInfo::NOTBOT ],
		];
	}

}
