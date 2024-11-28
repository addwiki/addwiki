<?php

namespace Wikibase\MediaInfo\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Int32EntityId;
use Wikibase\DataModel\Entity\SerializableEntityId;

/**
 * Identifier for media info entities, containing a numeric id prefixed by 'M'.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 * @author Thiemo Kreuz
 */
class MediaInfoId extends SerializableEntityId implements Int32EntityId {

	public const PATTERN = '/^M[1-9]\d{0,9}\z/i';

	/**
	 * @param string $idSerialization
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $idSerialization ) {
		$this->assertValidIdFormat( $idSerialization );
		parent::__construct( strtoupper( $idSerialization ) );
	}

	/**
	 * @param $idSerialization
	 * @return void
	 */
	private function assertValidIdFormat( $idSerialization ): void {
		if ( !is_string( $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must be a string' );
		}

		if ( !preg_match( self::PATTERN, $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must match ' . self::PATTERN );
		}

		if ( strlen( $idSerialization ) > 10
			&& substr( $idSerialization, 1 ) > Int32EntityId::MAX
		) {
			throw new InvalidArgumentException( '$idSerialization can not exceed '
				. Int32EntityId::MAX );
		}
	}

	/**
	 * @see Int32EntityId::getNumericId
	 *
	 * @return int Guaranteed to be a unique integer in the range [1..2147483647].
	 */
	public function getNumericId(): int {
		return (int)substr( $this->serialization, 1 );
	}

	/**
	 * @see EntityId::getEntityType
	 *
	 * @return string
	 */
	public function getEntityType(): string {
		return 'mediainfo';
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return [ 'serialization' => $this->serialization ];
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param array $serialized
	 */
	public function __unserialize( array $serialized ): void {
		$this->__construct( $serialized['serialization'] ?? '' );
		if ( $this->serialization !== $serialized['serialization'] ) {
			throw new InvalidArgumentException( '$value contained invalid serialization' );
		}
	}

}
