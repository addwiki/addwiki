<?php

namespace Addwiki\Wikibase\DataModel;

use DataValues\Deserializers\DataValueDeserializer;
use Deserializers\Deserializer;
use Deserializers\DispatchingDeserializer;
use Serializers\DispatchingSerializer;
use Serializers\Serializer;
use Wikibase\DataModel\Deserializers\DeserializerFactory;
use Wikibase\DataModel\Entity\DispatchingEntityIdParser;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Serializers\SerializerFactory;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;
use Wikibase\MediaInfo\DataModel\MediaInfoId;
use Wikibase\MediaInfo\DataModel\Serialization\MediaInfoDeserializer;
use Wikibase\MediaInfo\DataModel\Serialization\MediaInfoSerializer;

/**
 * Factory class for creating various data model serializers and deserializers.
 *
 * @access public
 */
class DataModelFactory {

	/**
	 * @var Deserializer Deserializer for data values.
	 */
	private Deserializer $dataValueDeserializer;

	/**
	 * @var Serializer Serializer for data values.
	 */
	private Serializer $dataValueSerializer;

	/**
	 * Constructor for DataModelFactory.
	 *
	 * @param Deserializer $dvDeserializer Deserializer for data values.
	 * @param Serializer $dvSerializer Serializer for data values.
	 */
	public function __construct( Deserializer $dvDeserializer, Serializer $dvSerializer ) {
		$this->dataValueDeserializer = $dvDeserializer;
		$this->dataValueSerializer = $dvSerializer;
	}

	/**
	 * Get the data value deserializer.
	 *
	 * @return Deserializer
	 */
	public function getDataValueDeserializer(): Deserializer {
		return $this->dataValueDeserializer;
	}

	/**
	 * Get the data value serializer.
	 *
	 * @return Serializer
	 */
	public function getDataValueSerializer(): Serializer {
		return $this->dataValueSerializer;
	}

	/**
	 * Create a new default data model serializer factory.
	 *
	 * @return SerializerFactory
	 */
	private function newDefaultDataModelSerializerFactory(): SerializerFactory {
		return new SerializerFactory( $this->dataValueSerializer );
	}

	/**
	 * Create a new default data model deserializer factory.
	 *
	 * @return DeserializerFactory
	 */
	private function newDefaultDataModelDeserializerFactory(): DeserializerFactory {
		return new DeserializerFactory(
			new DataValueDeserializer(),
			$this->newEntityIdParser(),
			new InMemoryDataTypeLookup(),
			[],
			[]
		);
	}

	/**
	 * Create a new entity ID parser.
	 *
	 * @return DispatchingEntityIdParser
	 */
	public function newEntityIdParser(): DispatchingEntityIdParser {
		$builders = [
			// Defaults in all Wikibases
			ItemId::PATTERN => static function ( $serialization ) {
				return new ItemId( $serialization );
			},
			NumericPropertyId::PATTERN => static function ( $serialization ) {
				return new NumericPropertyId( $serialization );
			},
			// The MediaInfo extension
			MediaInfoId::PATTERN => static function ( $serialization ) {
				return new MediaInfoId( $serialization );
			},
		];
		return new DispatchingEntityIdParser( $builders );
	}

	/**
	 * Create a new entity deserializer.
	 *
	 * @return Deserializer
	 */
	public function newEntityDeserializer(): Deserializer {
		$datamodelDeserializerFactory = $this->newDefaultDataModelDeserializerFactory();
		return new DispatchingDeserializer( [
			// Defaults in all Wikibases (Items and Properties)
			$datamodelDeserializerFactory->newEntityDeserializer(),
			// The MediaInfo extension
			new MediaInfoDeserializer(
				$datamodelDeserializerFactory->newEntityIdDeserializer(),
				$datamodelDeserializerFactory->newTermListDeserializer(),
				$datamodelDeserializerFactory->newStatementListDeserializer()
			),
		] );
	}

	/**
	 * Create a new entity serializer.
	 *
	 * @return Serializer
	 */
	public function newEntitySerializer(): Serializer {
		$datamodelSerializerFactory = $this->newDefaultDataModelSerializerFactory();
		return new DispatchingSerializer( [
			// Defaults in all Wikibases (Items and Properties)
			$datamodelSerializerFactory->newEntitySerializer(),
			// The MediaInfo extension
			new MediaInfoSerializer(
				$datamodelSerializerFactory->newTermListSerializer(),
				$datamodelSerializerFactory->newStatementListSerializer()
			),
		] );
	}

	/**
	 * Create a new statement deserializer.
	 *
	 * @return Deserializer
	 */
	public function newStatementDeserializer(): Deserializer {
		return $this->newDefaultDataModelDeserializerFactory()->newStatementDeserializer();
	}

	/**
	 * Create a new statement serializer.
	 *
	 * @return Serializer
	 */
	public function newStatementSerializer(): Serializer {
		return $this->newDefaultDataModelSerializerFactory()->newStatementSerializer();
	}

	/**
	 * Create a new reference serializer.
	 *
	 * @return Serializer
	 */
	public function newReferenceSerializer(): Serializer {
		return $this->newDefaultDataModelSerializerFactory()->newReferenceSerializer();
	}

}
