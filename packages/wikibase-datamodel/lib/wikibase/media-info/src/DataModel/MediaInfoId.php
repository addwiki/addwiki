<?php

namespace Wikibase\MediaInfo\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Int32EntityId;

/**
 * Identifier for media info entities, containing a numeric id prefixed by 'M'.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 * @author Thiemo Kreuz
 */
class MediaInfoId extends EntityId implements Int32EntityId {

	public const PATTERN = '/^M[1-9]\d{0,9}\z/i';

	/**
	 * @param string $idSerialization
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $idSerialization ) {
		$parts = self::splitSerialization( $idSerialization );
		$this->assertValidIdFormat( $parts[2] );
		parent::__construct( self::joinSerialization(
			[ $parts[0], $parts[1], strtoupper( $parts[2] ) ]
		) );
	}

	private function assertValidIdFormat( $idSerialization ) {
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
	public function getNumericId() {
		$serializationParts = self::splitSerialization( $this->serialization );
		return (int)substr( $serializationParts[2], 1 );
	}

	/**
	 * @see EntityId::getEntityType
	 *
	 * @return string
	 */
	public function getEntityType() {
		return 'mediainfo';
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return $this->serialization;
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 */
	public function unserialize( $value ) {
		$this->serialization = $value;
		list( $this->repositoryName, $this->localPart ) = self::extractRepositoryNameAndLocalPart(
			$value
		);
	}

}
