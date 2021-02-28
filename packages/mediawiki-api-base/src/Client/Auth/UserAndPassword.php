<?php

namespace Addwiki\Mediawiki\Api\Client\Auth;

use InvalidArgumentException;

/**
 * For use with plain MediaWiki logins
 */
class UserAndPassword implements AuthMethod {

	private string $password;
	private string $username;

	public function __construct( string $username, string $password ) {
		if ( empty( $username ) || empty( $password ) ) {
			throw new InvalidArgumentException( 'Username and Password are not allowed to be empty' );
		}
		$this->username = $username;
		$this->password = $password;
	}

	public function getUsername(): string {
		return $this->username;
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function equals( UserAndPassword $other ): bool {
		return $this->getUsername() === $other->getUsername()
			&& $this->getPassword() === $other->getPassword();
	}

}
