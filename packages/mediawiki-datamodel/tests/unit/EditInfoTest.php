<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\EditInfo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\EditInfo
 * @author Addshore
 */
class EditInfoTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param string[]|bool[] $sum
	 * @param string[]|bool[] $minor
	 * @param string[]|bool[] $bot
	 */
	public function testValidConstruction( array $sum, array $minor, array $bot ): void {
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

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param int[] $sum
	 * @param int[]|string[]|bool[] $minor
	 * @param int[]|string[]|bool[] $bot
	 */
	public function testInvalidConstruction( array $sum, array $minor, array $bot ): void {
		$this->expectException( InvalidArgumentException::class );
		new EditInfo( $sum, $minor, $bot );
	}

	public function provideInvalidConstruction(): array {
		return [
		[ 1, 2, 3 ],
		[ "foo", false, 3 ],
		[ "foo", 3, false ],
		[ [], true, false ],
		];
	}

}
