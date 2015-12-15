<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use stdClass;

/**
 * @author Addshore
 */
class MicroData {

	/**
	 * @var array[]
	 */
	private $properties = array();

	/**
	 * @var string[]
	 */
	private $types = array();

	/**
	 * @var string|null
	 */
	private $id = null;

	/**
	 * @param stdClass $object that can have the following defined:
	 *     - properties
	 *     - type
	 *     - id
	 * As returned by MicrodataPhp::getObject
	 */
	public function __construct( stdClass $object = null ) {
		if( $object === null ) {
			return;
		}
		if( isset( $object->properties ) ) {
			foreach( $object->properties as $name => $values ) {
				foreach( $values as $value ) {
					$this->properties[$name][] = new self( $value );
				}
			}
		}
		if( isset( $object->type ) ) {
			$this->types = $object->type;
		}
		if( isset( $object->id ) ) {
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
		//TODO do we need to check https here too?
		return in_array( $type, $this->types ) || in_array( "http://schema.org/" . $type, $this->types );
	}

	/**
	 * @return array
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @param string $name
	 *
	 * @return self[]
	 */
	public function getProperty( $name ) {
		if( $this->hasProperty( $name ) ) {
			return $this->properties[$name];
		}
		return array();
	}

	/**
	 * @param string $name
	 *
	 * @return self|null
	 */
	public function getFirstProperty( $name ) {
		if( $this->hasProperty( $name ) ) {
			return $this->properties[$name][0];
		}
		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasProperty( $name ) {
		return array_key_exists( $name, $this->properties );
	}

}
