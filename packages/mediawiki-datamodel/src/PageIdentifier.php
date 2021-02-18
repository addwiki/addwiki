<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;

class PageIdentifier implements JsonSerializable {

	private ?int $id;

	private ?Title $title;

	/**
	 * @param Title|null $title
	 * @throws InvalidArgumentException
	 */
	public function __construct( Title $title = null, ?int $id = null ) {
		if ( !is_int( $id ) && $id !== null ) {
			throw new InvalidArgumentException( '$id must be an int' );
		}
		$this->title = $title;
		$this->id = $id;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	public function getTitle(): ?Title {
		return $this->title;
	}

	/**
	 * Does this object identify a page
	 */
	public function identifiesPage(): bool {
		return !( !$this->title instanceof Title && $this->id === null );
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return array<string mixed>
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

	public static function jsonDeserialize( array $array ): \Addwiki\Mediawiki\DataModel\PageIdentifier {
		return new self(
		isset( $array['title'] ) ? Title::jsonDeserialize( $array['title'] ) : null,
		$array['id'] ?? null

		);
	}
}
