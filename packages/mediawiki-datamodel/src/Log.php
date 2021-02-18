<?php

namespace Addwiki\Mediawiki\DataModel;

use JsonSerializable;

/**
 * @since 0.5
 */
class Log implements JsonSerializable {

	private int $id;

	private string $type;

	private string $action;

	private string $timestamp;

	private string $user;

	private string $comment;

	private PageIdentifier $pageIdentifier;

	private array $details = [];

	/**
	 * @param mixed[] $details
	 */
	public function __construct( int $id, string $type, string $action, string $timestamp, string $user, PageIdentifier $pageIdentifier, string $comment, array $details ) {
		$this->id = $id;
		$this->type = $type;
		$this->action = $action;
		$this->timestamp = $timestamp;
		$this->user = $user;
		$this->pageIdentifier = $pageIdentifier;
		$this->comment = $comment;
		$this->details = $details;
	}

	/**
	 * @since 0.5
	 */
	public function getUser(): string {
		return $this->user;
	}

	/**
	 * @since 0.5
	 */
	public function getAction(): string {
		return $this->action;
	}

	/**
	 * @since 0.5
	 */
	public function getComment(): string {
		return $this->comment;
	}

	/**
	 * @since 0.5
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @since 0.6
	 */
	public function getPageIdentifier(): PageIdentifier {
		return $this->pageIdentifier;
	}

	/**
	 * @since 0.5
	 */
	public function getTimestamp(): string {
		return $this->timestamp;
	}

	/**
	 * @since 0.5
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @since 0.5
	 * @return mixed[]
	 */
	public function getDetails(): array {
		return $this->details;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		return [
		'id' => $this->id,
		'type' => $this->type,
		'action' => $this->action,
		'timestamp' => $this->timestamp,
		'user' => $this->user,
		'pageidentifier' => $this->pageIdentifier,
		'comment' => $this->comment,
		'details' => $this->details,
		];
	}

	public static function jsonDeserialize( array $json ): Log {
		return new self(
		$json['id'],
		$json['type'],
		$json['action'],
		$json['timestamp'],
		$json['user'],
		PageIdentifier::jsonDeserialize( $json['pageidentifier'] ),
		$json['comment'],
		$json['details']
		);
	}

}
