<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\User
 */
class UserTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param array<string, mixed[]> $groups
	 * @param mixed[] $rights
	 */
	public function testValidConstruction( string $name, int $id, int $editcount, ?string $registration, array $groups, array $rights, string $gender ): void {
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

	/**
	 * @return array<int, array<int|string|mixed[]|array<string, mixed[]>|null>>
	 */
	public function provideValidConstruction(): array {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		[ 'Username', 2, 2, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, null, [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param string $registration
	 * @param array<string, mixed[]>|mixed[] $groups
	 * @param mixed[] $rights
	 */
	public function testInvalidConstruction( string $name, int $id, int $editcount, ?string $registration, array $groups, array $rights, string $gender ): void {
		$this->expectException( InvalidArgumentException::class );
		new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
	}

	/**
	 * @return array<int, array<int|string|mixed[]|array<string, mixed[]>>>
	 */
	public function provideInvalidConstruction(): array {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'foo' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [], [], 'male' ],
		];
	}

}
