<?php

namespace Addwiki\Cli\Config;

use ArrayAccess;
use LogicException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Addshore
 */
class AppConfig implements ArrayAccess {

	private $configDirectory;
	private $configFilename = 'aww.yml';
	private $path;

	private $data;
	private $isLoaded;

	public function __construct( $pwd ) {
		$this->configDirectory = $pwd . '/config';
		$this->path = $this->configDirectory . DIRECTORY_SEPARATOR . $this->configFilename;
	}

	private function load() {
		$this->isLoaded = true;
		$this->createIfNotExists();
		$data = Yaml::parse( file_get_contents( $this->path ) );
		// If the file is empty this will eval to null, so change it back to an array
		if ( $data === null ) {
			$data = [];
		}
		$this->data = $data;
	}

	private function save() {
		file_put_contents( $this->path, Yaml::dump( $this->data ) );
	}

	private function createIfNotExists() {
		if ( !file_exists( $this->path ) ) {
			file_put_contents( $this->path, Yaml::dump( [] ) );
		}
	}

	public function get( $name, $default = null ) {
		$this->loadIfNotLoaded();

		$temp = &$this->data;
		$paths = explode( '.', $name );
		foreach ( $paths as $i => $key ) {
			if ( ( $i + 1 ) == count( $paths ) ) {
				if ( $temp === null || !array_key_exists( $key, $temp ) ) {
					return $default;
				} else {
					return $temp[$key];
				}
			} else {
				$temp = &$temp[$key];
			}

		}
		throw new LogicException();
	}

	public function set( $name, $value ) {
		$temp = &$this->data;
		foreach ( explode( '.', $name ) as $key ) {
			$temp = &$temp[$key];
		}
		$temp = $value;
		unset( $temp );

		$this->save();
	}

	public function has( $name ) {
		return $this->get( $name ) !== null;
	}

	private function loadIfNotLoaded() {
		if ( !$this->isLoaded ) {
			$this->load();
		}
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		$this->loadIfNotLoaded();
		return empty( $this->data );
	}

	public function offsetExists( $offset ) {
		return $this->has( $offset );
	}

	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	public function offsetSet( $offset, $value ) {
		$this->set( $offset, $value );
	}

	public function offsetUnset( $offset ) {
		$this->set( $offset, null );
	}

}
