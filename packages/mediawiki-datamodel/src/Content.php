<?php

namespace Addwiki\Mediawiki\DataModel;

use LogicException;

/**
 * Represents the content of a revision
 */
class Content {

	/**
	 * @var string sha1 hash of the object content upon creation
	 */
	private string $initialHash;

	/**
	 * @var mixed
	 */
	private $data;

	private ?string $model;

	/**
	 * Should always be called AFTER overriding constructors so a hash can be created
	 *
	 * @param mixed $data
	 */
	public function __construct( $data, ?string $model = null ) {
		$this->data = $data;
		$this->model = $model;
		$this->initialHash = $this->getHash();
	}

	public function getModel(): ?string {
		return $this->model;
	}

	/**
	 * Returns a sha1 hash of the content
	 *
	 * @throws LogicException
	 * @return mixed|string
	 */
	public function getHash() {
		$data = $this->getData();
		if ( is_object( $data ) ) {
			if ( method_exists( $data, 'getHash' ) ) {
				return $data->getHash();
			} else {
				return sha1( serialize( $data ) );
			}
		}
		if ( is_string( $data ) ) {
			return sha1( $data );
		}
		throw new LogicException( "Cant get hash for data of type: " . gettype( $data ) );
	}

	/**
	 * Has the content been changed since object construction (this shouldn't happen!)
	 */
	public function hasChanged(): bool {
		return $this->initialHash !== $this->getHash();
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

}
