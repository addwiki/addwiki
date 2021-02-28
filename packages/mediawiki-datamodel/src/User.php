<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * Represents a mediawiki user
 */
class User {

	private string $name;
	private int $id;
	private int $editcount;
	private ?string $registration;
	private array $groups = [];
	private array $rights = [];
	private string $gender;

	/**
	 * @param array[] $groups groups grouped by type.
	 *                              Keys to use are
	 *                              'groups' and
	 *                              'implicitgroups' as
	 *                              returned by the api.
	 *
	 * @throws InvalidArgumentException
	 * @param mixed[] $rights
	 */
	public function __construct( string $name, int $id, int $editcount, ?string $registration, array $groups, array $rights, string $gender ) {
		if ( empty( $name ) ) {
			throw new InvalidArgumentException( '$name must be non empty string' );
		}
		if ( !is_array( $groups ) || !array_key_exists( 'groups', $groups ) || !array_key_exists( 'implicitgroups', $groups ) ) {
			throw new InvalidArgumentException( '$groups must be an array or arrays with keys "groups" and "implicitgroups"' );
		}

		$this->editcount = $editcount;
		$this->gender = $gender;
		$this->groups = $groups;
		$this->id = $id;
		$this->name = $name;
		$this->registration = $registration;
		$this->rights = $rights;
	}

	public function getEditcount(): int {
		return $this->editcount;
	}

	public function getGender(): string {
		return $this->gender;
	}

	/**
	 * @param string $type 'groups' or 'implicitgroups'
	 *
	 * @return mixed[]
	 */
	public function getGroups( string $type = 'groups' ): array {
		return $this->groups[$type];
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getRegistration(): ?string {
		return $this->registration;
	}

	/**
	 * @return mixed[]
	 */
	public function getRights(): array {
		return $this->rights;
	}

}
