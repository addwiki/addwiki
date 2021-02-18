<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;

/**
 * @author Addshore
 */
class Title implements JsonSerializable {

	private string $title;

	private int $ns;

	/**
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( string $title, int $ns = 0 ) {
		if ( !is_string( $title ) || empty( $title ) ) {
			throw new InvalidArgumentException( '$title must be a string' );
		}
		if ( !is_int( $ns ) ) {
			throw new InvalidArgumentException( '$ns must be an int' );
		}
		$this->title = $title;
		$this->ns = $ns;
	}

	/**
	 * @since 0.1
	 */
	public function getNs(): int {
		return $this->ns;
	}

	/**
	 * @since 0.6
	 */
	public function getText(): string {
		return $this->title;
	}

	/**
	 * @deprecated in 0.6 use getText (makes things look cleaner)
	 */
	public function getTitle(): string {
		return $this->getText();
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return array [ 'title' => string, 'ns' => int ]
	 */
	public function jsonSerialize() {
		return [
		'title' => $this->title,
		'ns' => $this->ns,
		];
	}

	public static function jsonDeserialize( array $json ): Title {
		return new self( $json['title'], $json['ns'] );
	}

}
