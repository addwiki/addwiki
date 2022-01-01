<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData;

use stdClass;

class MicroData {

	/**
	 * @var int
	 */
	public const PROP_STRING = 1;

	/**
	 * @var int
	 */
	public const PROP_DATA = 2;

	/**
	 * @var array[]
	 */
	private array $properties = [];

	/**
	 * @var string[]
	 */
	private array $types = [];

	private ?string $id;

	/**
	 * @param stdClass|null $object that can have the following defined:
	 *     - properties
	 *     - type
	 *     - id
	 * As returned by MicrodataPhp::getObject
	 */
	public function __construct( stdClass $object = null ) {
		if ( $object === null ) {
			return;
		}

		if ( property_exists( $object, 'properties' ) && $object->properties !== null ) {
			foreach ( $object->properties as $name => $values ) {
				foreach ( $values as $value ) {
					$this->properties[$name][] = is_string( $value ) ? $value : new self( $value );
				}
			}
		}

		if ( property_exists( $object, 'type' ) && $object->type !== null ) {
			$this->types = $object->type;
		}

		if ( property_exists( $object, 'id' ) && $object->id !== null ) {
			$this->id = $object->id;
		}
	}

	public function getId(): ?string {
		return $this->id;
	}

	/**
	 * @return string[]
	 */
	public function getTypes(): array {
		return $this->types;
	}

	public function hasType( string $type ): bool {
		return in_array( $type, $this->types ) || in_array( "http://schema.org/" . $type, $this->types );
	}

	/**
	 * @return array[]
	 */
	public function getProperties(): array {
		return $this->properties;
	}

	/**
	 * @param int|null $propType self::PROP_* constant
	 * @return mixed[]
	 */
	public function getProperty( string $name, ?int $propType = null ): array {
		$properties = [];
		if ( array_key_exists( $name, $this->properties ) ) {
			foreach ( $this->properties[$name] as $property ) {
				if ( $this->propertyIsOfPropType( $property, $propType ) ) {
					$properties[] = $property;
				}
			}
		}

		return $properties;
	}

	/**
	 * @param int|null $propType self::PROP_* constant
	 * @return mixed|null
	 */
	public function getFirstProperty( string $name, ?int $propType = null ) {
		if ( array_key_exists( $name, $this->properties ) ) {
			foreach ( $this->properties[$name] as $property ) {
				if ( $this->propertyIsOfPropType( $property, $propType ) ) {
					return $property;
				}
			}
		}

		return null;
	}

	/**
	 * @param int|null $propType self::PROP_* constant
	 *
	 */
	public function hasProperty( string $name, ?int $propType = null ): bool {
		return array_key_exists( $name, $this->properties ) &&
			$this->getFirstProperty( $name, $propType ) !== null;
	}

	/**
	 * @param string|MicroData $property
	 * @param int|null|null $type self::PROP_* constant
	 */
	private function propertyIsOfPropType( $property, ?int $type = null ): bool {
		if ( $type === null ) {
			return true;
		}

		if ( $type === self::PROP_STRING && is_string( $property ) ) {
			return true;
		}

		return $type === self::PROP_DATA && $property instanceof self;
	}

}
