<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\User
 * @author Addshore
 */
class UserTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $name
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $id
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $editcount
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[]|null[] $registration
	 */
	public function testValidConstruction( array $name, array $id, array $editcount, array $registration, $groups, $rights, $gender ): void {
		$user = new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
		$this->assertEquals( $name, $user->getName() );
		$this->assertEquals( $id, $user->getId() );
		$this->assertEquals( $editcount, $user->getEditcount() );
		$this->assertEquals( $registration, $user->getRegistration() );
		$this->assertEquals( $groups['groups'], $user->getGroups() );
		$this->assertEquals( $groups['implicitgroups'], $user->getGroups( 'implicitgroups' ) );
		$this->assertEquals( $rights, $user->getRights() );
		$this->assertEquals( $gender, $user->getGender() );
	}

	public function provideValidConstruction(): array {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		[ 'Username', 99_999_999, 99_999_997, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, null, [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param int[]|string[]|mixed[][] $name
	 * @param int[]|string[]|array<string, mixed[]>[] $id
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $editcount
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $registration
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $groups
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $rights
	 * @param int[]|string[]|mixed[][]|array<string, mixed[]>[] $gender
	 */
	public function testInvalidConstruction( array $name, array $id, array $editcount, array $registration, array $groups, array $rights, array $gender ): void {
		$this->expectException( InvalidArgumentException::class );
		new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
	}

	public function provideInvalidConstruction(): array {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', 'bad', [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], 'bad', 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 1 ],
		[ 'Username', 1, 'bad', 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 'bad', 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 14_287_941, 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'foo' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [], [], 'male' ],
		];
	}

}
