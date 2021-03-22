<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a collection or revisions
 */
class Revisions {

	/**
	 * @var Revision[]
	 */
	private array $revisions = [];

	/**
	 * @param Revisions[]|Revisions $revisions
	 */
	public function __construct( $revisions = [] ) {
		$this->revisions = [];
		$this->addRevisions( $revisions );
	}

	/**
	 * @param Revision[]|Revisions $revisions
	 *
	 * @throws InvalidArgumentException
	 */
	public function addRevisions( $revisions ): void {
		if ( !is_array( $revisions ) && !$revisions instanceof Revisions ) {
			throw new InvalidArgumentException( '$revisions needs to either be an array or a Revisions object' );
		}
		if ( $revisions instanceof Revisions ) {
			$revisions = $revisions->toArray();
		}
		foreach ( $revisions as $revision ) {
			$this->addRevision( $revision );
		}
	}

	public function addRevision( Revision $revision ): void {
		$this->revisions[$revision->getId()] = $revision;
	}

	public function hasRevisionWithId( int $id ): bool {
		return array_key_exists( $id, $this->revisions );
	}

	public function hasRevision( Revision $revision ): bool {
		return array_key_exists( $revision->getId(), $this->revisions );
	}

	/**
	 * @return Revision|null Revision or null if there is no revision
	 */
	public function getLatest(): ?Revision {
		if ( empty( $this->revisions ) ) {
			return null;
		}
		return $this->revisions[ max( array_keys( $this->revisions ) ) ];
	}

	/**
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function get( int $revid ): Revision {
		if ( !is_int( $revid ) ) {
			throw new InvalidArgumentException( '$revid needs to be an int' );
		}
		if ( $this->hasRevisionWithId( $revid ) ) {
			return $this->revisions[$revid];
		}
		throw new RuntimeException( 'No such revision loaded in Revisions object' );
	}

	/**
	 * @return Revision[]
	 */
	public function toArray(): array {
		return $this->revisions;
	}
}
