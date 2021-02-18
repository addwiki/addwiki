<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;

class PageIdentifier implements JsonSerializable {

	/**
	 * @var int|null
	 */
	private $id;

	/**
	 * @var Title|null
	 */
	private $title;

	/**
	 * @param Title|null $title
	 * @param int|null $id
	 * @throws InvalidArgumentException
	 */
	public function __construct( Title $title = null, $id = null ) {
		if ( !is_int( $id ) && $id !== null ) {
			throw new InvalidArgumentException( '$id must be an int' );
		}
		$this->title = $title;
		$this->id = $id;
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return Title|null
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Does this object identify a page
	 *
	 * @return bool
	 */
	public function identifiesPage() {
		return !( !$this->title instanceof Title && $this->id === null );
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		$array = [];
		if ( $this->id !== null ) {
			$array['id'] = $this->id;
		}
		if ( $this->title !== null ) {
			$array['title'] = $this->title->jsonSerialize();
		}
		return $array;
	}

	/**
	 * @param array $array
	 *
	 * @return self
	 */
	public static function jsonDeserialize( $array ) {
		return new self(
		isset( $array['title'] ) ? Title::jsonDeserialize( $array['title'] ) : null,
		$array['id'] ?? null

		);
	}
}
