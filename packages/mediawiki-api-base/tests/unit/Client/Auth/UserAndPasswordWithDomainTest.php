<?php

namespace Addwiki\Mediawiki\Api\Tests\Unit\Client\Auth;

use Addwiki\Mediawiki\Api\Client\Auth\UserAndPasswordWithDomain;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mediawiki\Api\Client\Auth\UserAndPasswordWithDomain
 */
class UserAndPasswordWithDomainTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 * @param string $domain
	 */
	public function testValidConstruction( string $user, string $pass, ?string $domain = null ): void {
		$userAndPasswordWithDomain = new UserAndPasswordWithDomain( $user, $pass, $domain );
		$this->assertSame( $user, $userAndPasswordWithDomain->getUsername() );
		$this->assertSame( $pass, $userAndPasswordWithDomain->getPassword() );
		$this->assertSame( $domain, $userAndPasswordWithDomain->getDomain() );
	}

	/**
	 * @return array<int, array<string>>
	 */
	public function provideValidConstruction(): array {
		return [
			[ 'user', 'pass' ],
			[ 'user', 'pass', 'domain' ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 * @param string $domain
	 */
	public function testInvalidConstruction( string $user, string $pass, ?string $domain = null ): void {
		$this->expectException( InvalidArgumentException::class );
		 new UserAndPasswordWithDomain( $user, $pass, $domain );
	}

	/**
	 * @return array<int, array<string>>
	 */
	public function provideInvalidConstruction(): array {
		return [
			[ 'user', '' ],
			[ '', 'pass' ],
			[ '', '' ],
			[ '', '', '' ],
			[ 'aaa', 'aaa', '' ],
		];
	}

	/**
	 * @dataProvider provideTestEquals
	 */
	public function testEquals( UserAndPasswordWithDomain $user1, UserAndPasswordWithDomain $user2, bool $shouldEqual ): void {
		$this->assertSame( $shouldEqual, $user1->equals( $user2 ) );
		$this->assertSame( $shouldEqual, $user2->equals( $user1 ) );
	}

	/**
	 * @return array<int, array<UserAndPasswordWithDomain|bool>>
	 */
	public function provideTestEquals(): array {
		return [
			[ new UserAndPasswordWithDomain( 'usera', 'passa' ), new UserAndPasswordWithDomain( 'usera', 'passa' ), true ],
			[ new UserAndPasswordWithDomain( 'usera', 'passa', 'domain' ), new UserAndPasswordWithDomain( 'usera', 'passa', 'domain' ), true ],
			[ new UserAndPasswordWithDomain( 'DIFF', 'passa' ), new UserAndPasswordWithDomain( 'usera', 'passa' ), false ],
			[ new UserAndPasswordWithDomain( 'usera', 'DIFF' ), new UserAndPasswordWithDomain( 'usera', 'passa' ), false ],
			[ new UserAndPasswordWithDomain( 'usera', 'passa' ), new UserAndPasswordWithDomain( 'DIFF', 'passa' ), false ],
			[ new UserAndPasswordWithDomain( 'usera', 'passa' ), new UserAndPasswordWithDomain( 'usera', 'DIFF' ), false ],
		];
	}

}
