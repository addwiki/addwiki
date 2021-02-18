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
	 */
	public function testValidConstruction( $name, $id, $editcount, $registration, $groups, $rights, $gender ) {
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

	public function provideValidConstruction() {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		[ 'Username', 99999999, 99999997, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, null, [ 'groups' => [], 'implicitgroups' => [] ], [], 'female' ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $name, $id, $editcount, $registration, $groups, $rights, $gender ) {
		$this->expectException( InvalidArgumentException::class );
		new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
	}

	public function provideInvalidConstruction() {
		return [
		[ 'Username', 1, 1, 'TIMESTAMP', 'bad', [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], 'bad', 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 1 ],
		[ 'Username', 1, 'bad', 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 'bad', 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 14287941, 1, 1, 'TIMESTAMP', [ 'groups' => [], 'implicitgroups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [], 'foo' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [ 'groups' => [] ], [], 'male' ],
		[ 'Username', 1, 1, 'TIMESTAMP', [], [], 'male' ],
		];
	}

}
