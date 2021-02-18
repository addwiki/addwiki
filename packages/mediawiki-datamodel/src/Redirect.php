<?php

namespace Addwiki\Mediawiki\DataModel;

use JsonSerializable;

class Redirect implements JsonSerializable {

	private Title $from;
	private Title $to;

	public function __construct( Title $from, Title $to ) {
		$this->from = $from;
		$this->to = $to;
	}

	public function getFrom(): Title {
		return $this->from;
	}

	public function getTo(): Title {
		return $this->to;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return array <string mixed>
	 */
	public function jsonSerialize() {
		return [
		'from' => $this->from->jsonSerialize(),
		'to' => $this->to->jsonSerialize(),
		];
	}

	public static function jsonDeserialize( array $json ): Redirect {
		return new self(
		Title::jsonDeserialize( $json['from'] ),
		Title::jsonDeserialize( $json['to'] )
		);
	}

}
