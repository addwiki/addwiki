<?php

namespace Addwiki\Wikibase\DataModel;

use Deserializers\Deserializer;
use Deserializers\DispatchingDeserializer;
use Serializers\DispatchingSerializer;
use Serializers\Serializer;
use Wikibase\DataModel\DeserializerFactory;
use Wikibase\DataModel\Entity\DispatchingEntityIdParser;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\SerializerFactory;
use Wikibase\MediaInfo\DataModel\MediaInfoId;
use Wikibase\MediaInfo\DataModel\Serialization\MediaInfoDeserializer;
use Wikibase\MediaInfo\DataModel\Serialization\MediaInfoSerializer;

/**
 * @access public
 */
class DataModelFactory {

	private Deserializer $dataValueDeserializer;

	private Serializer $dataValueSerializer;

	public function __construct( Deserializer $dvDeserializer, Serializer $dvSerializer ) {
		$this->dataValueDeserializer = $dvDeserializer;
		$this->dataValueSerializer = $dvSerializer;
	}

	public function getDataValueDeserializer(): Deserializer {
		return $this->dataValueDeserializer;
	}

	public function getDataValueSerializer(): Serializer {
		return $this->dataValueSerializer;
	}

	private function newDefaultDataModelSerializerFactory(): SerializerFactory {
		return new SerializerFactory( $this->dataValueSerializer );
	}

	private function newDefaultDataModelDeserializerFactory(): DeserializerFactory {
		return new DeserializerFactory(
			$this->dataValueDeserializer,
			$this->newEntityIdParser()
		);
	}

	public function newEntityIdParser() {
		$builders = [
			// Defaults in all Wikibases
			ItemId::PATTERN => static function ( $serialization ) {
				return new ItemId( $serialization );
			},
			PropertyId::PATTERN => static function ( $serialization ) {
				return new PropertyId( $serialization );
			},
			// The MediaInfo extension
			MediaInfoId::PATTERN => static function ( $serialization ) {
				return new MediaInfoId( $serialization );
			},
		];
		return new DispatchingEntityIdParser( $builders );
	}

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

	public function newStatementDeserializer(): Deserializer {
		return $this->newDefaultDataModelDeserializerFactory()->newStatementDeserializer();
	}

	public function newStatementSerializer(): Serializer {
		return $this->newDefaultDataModelSerializerFactory()->newStatementSerializer();
	}

	public function newReferenceSerializer(): Serializer {
		return $this->newDefaultDataModelSerializerFactory()->newReferenceSerializer();
	}

}
