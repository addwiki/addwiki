<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer\MicroData;

use stdClass;

/**
 * @author Addshore
 */
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
	private $properties = [];

	/**
	 * @var string[]
	 */
	private $types = [];

	/**
	 * @var string|null
	 */
	private $id;

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

	/**
	 * @return null|string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string[]
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function hasType( $type ) {
		return in_array( $type, $this->types ) || in_array( "http://schema.org/" . $type, $this->types );
	}

	/**
	 * @return array[]
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @param string $name
	 * @param int|null $propType self::PROP_* constant
	 *
	 * @return self[]|string[]
	 */
	public function getProperty( $name, $propType = null ) {
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
	 * @param string $name
	 * @param int|null $propType self::PROP_* constant
	 *
	 * @return self|string|null
	 */
	public function getFirstProperty( $name, $propType = null ) {
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
	 * @param string $name
	 * @param int|null $propType self::PROP_* constant
	 *
	 * @return bool
	 */
	public function hasProperty( $name, $propType = null ) {
		return array_key_exists( $name, $this->properties ) &&
			$this->getFirstProperty( $name, $propType ) !== null;
	}

	/**
	 * @param string|MicroData $property
	 * @param int| null|null $type self::PROP_* constant
	 *
	 * @return bool
	 */
	private function propertyIsOfPropType( $property, $type = null ) {
		if ( $type === null ) {
			return true;
		}
		if ( $type === self::PROP_STRING && is_string( $property ) ) {
			return true;
		}
		return $type === self::PROP_DATA && $property instanceof self;
	}

}
