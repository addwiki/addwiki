<?php

namespace Addwiki\Cli\Config;

use ArrayAccess;
use LogicException;
use Symfony\Component\Yaml\Yaml;

/**
 * @psalm-suppress MissingTemplateParam
 */
class AppConfig implements ArrayAccess {

	private string $configDirectory;

	private string $configFilename = 'aww.yml';

	private string $path;

	private $data;

	private ?bool $isLoaded = null;

	public function __construct( $pwd ) {
		$this->configDirectory = $pwd . '/config';
		$this->path = $this->configDirectory . DIRECTORY_SEPARATOR . $this->configFilename;
	}

	private function load(): void {
		$this->isLoaded = true;
		$this->createIfNotExists();
		$data = Yaml::parse( file_get_contents( $this->path ) );
		// If the file is empty this will eval to null, so change it back to an array
		if ( $data === null ) {
			$data = [];
		}

		$this->data = $data;
	}

	private function save(): void {
		file_put_contents( $this->path, Yaml::dump( $this->data ) );
	}

	private function createIfNotExists(): void {
		if ( !file_exists( $this->path ) ) {
			file_put_contents( $this->path, Yaml::dump( [] ) );
		}
	}

	public function get( $name, $default = null ) {
		$this->loadIfNotLoaded();

		/**
		 * @psalm-suppress UnsupportedPropertyReferenceUsage
		 */
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

	public function set( $name, $value ): void {
		/**
		 * @psalm-suppress UnsupportedPropertyReferenceUsage
		 */
		$temp = &$this->data;
		foreach ( explode( '.', $name ) as $key ) {
			$temp = &$temp[$key];
		}

		$temp = $value;
		unset( $temp );

		$this->save();
	}

	public function has( $name ): bool {
		return $this->get( $name ) !== null;
	}

	private function loadIfNotLoaded(): void {
		if ( !$this->isLoaded ) {
			$this->load();
		}
	}

	public function isEmpty(): bool {
		$this->loadIfNotLoaded();
		return empty( $this->data );
	}

	public function offsetExists( $offset ): bool {
		return $this->has( $offset );
	}

	public function offsetGet( $offset ): mixed {
		return $this->get( $offset );
	}

	public function offsetSet( $offset, $value ): void {
		$this->set( $offset, $value );
	}

	public function offsetUnset( $offset ): void {
		$this->set( $offset, null );
	}

}
