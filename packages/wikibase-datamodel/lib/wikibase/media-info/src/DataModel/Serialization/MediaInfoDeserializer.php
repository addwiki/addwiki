<?php

namespace Wikibase\MediaInfo\DataModel\Serialization;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Deserializers\TypedObjectDeserializer;
use InvalidArgumentException;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;
use Wikibase\MediaInfo\DataModel\MediaInfo;
use Wikibase\MediaInfo\DataModel\MediaInfoId;

/**
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class MediaInfoDeserializer extends TypedObjectDeserializer {
	/**
	 * @var Deserializer
	 */
	private $idDeserializer;

	/**
	 * @var Deserializer
	 */
	private $termListDeserializer;

	/**
	 * @var Deserializer
	 */
	private $statementListDeserializer;

	/**
	 * @param Deserializer $idDeserializer
	 * @param Deserializer $termListDeserializer
	 * @param Deserializer $statementListDeserializer
	 */
	public function __construct(
		Deserializer $idDeserializer,
		Deserializer $termListDeserializer,
		Deserializer $statementListDeserializer
	) {
		parent::__construct( 'mediainfo', 'type' );

		$this->idDeserializer = $idDeserializer;
		$this->termListDeserializer = $termListDeserializer;
		$this->statementListDeserializer = $statementListDeserializer;
	}

	/**
	 * @param mixed $serialization
	 *
	 * @throws DeserializationException
	 * @return MediaInfo
	 */
	public function deserialize( $serialization ) {
		$this->assertCanDeserialize( $serialization );

		return new MediaInfo(
			$this->deserializeId( $serialization ),
			$this->deserializeLabels( $serialization ),
			$this->deserializeDescriptions( $serialization ),
			$this->deserializeStatements( $serialization )
		);
	}

	/**
	 * @param array $serialization
	 *
	 * @return MediaInfoId|null
	 */
	private function deserializeId( array $serialization ) {
		if ( array_key_exists( 'id', $serialization ) ) {
			$id = $this->idDeserializer->deserialize( $serialization['id'] );

			if ( !$id instanceof MediaInfoId ) {
				throw new InvalidArgumentException(
					'Expected MediaInfoId, got a ' . get_class( $id ) .
					' from deserializing ' . var_export( $serialization, true )
				);
			}

			return $id;
		}

		return null;
	}

	/**
	 * @param array $serialization
	 *
	 * @return TermList|null
	 */
	private function deserializeLabels( array $serialization ) {
		if ( array_key_exists( 'labels', $serialization ) ) {
			return $this->termListDeserializer->deserialize( $serialization['labels'] );
		}

		return null;
	}

	/**
	 * @param array $serialization
	 *
	 * @return TermList|null
	 */
	private function deserializeDescriptions( array $serialization ) {
		if ( array_key_exists( 'descriptions', $serialization ) ) {
			return $this->termListDeserializer->deserialize( $serialization['descriptions'] );
		}

		return null;
	}

	/**
	 * @param array $serialization
	 *
	 * @return StatementList|null
	 */
	private function deserializeStatements( array $serialization ) {
		if ( array_key_exists( 'statements', $serialization ) ) {
			return $this->statementListDeserializer->deserialize( $serialization['statements'] );
		}

		return null;
	}

}
