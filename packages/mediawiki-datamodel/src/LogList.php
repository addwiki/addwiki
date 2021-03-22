<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

/**
 * Represents a collection of Log classes
 */
class LogList implements JsonSerializable {

	/**
	 * @var Log[]
	 */
	private array $logs = [];

	/**
	 * @param Log[] $logs
	 */
	public function __construct( array $logs = [] ) {
		$this->logs = [];
		$this->addLogs( $logs );
	}

	/**
	 * @param Log[]|LogList $logs
	 *
	 * @throws InvalidArgumentException
	 */
	public function addLogs( $logs ): void {
		if ( !is_array( $logs ) && !$logs instanceof LogList ) {
			throw new InvalidArgumentException( '$logs needs to either be an array or a LogList object' );
		}
		if ( $logs instanceof LogList ) {
			$logs = $logs->toArray();
		}
		foreach ( $logs as $log ) {
			$this->addLog( $log );
		}
	}

	public function addLog( Log $log ): void {
		$this->logs[$log->getId()] = $log;
	}

	public function hasLogWithId( int $id ): bool {
		return array_key_exists( $id, $this->logs );
	}

	public function hasLog( Log $log ): bool {
		return array_key_exists( $log->getId(), $this->logs );
	}

	/**
	 * @return Log|null Log or null if there is no log
	 */
	public function getLatest(): ?Log {
		if ( empty( $this->logs ) ) {
			return null;
		}
		return $this->logs[ max( array_keys( $this->logs ) ) ];
	}

	/**
	 * @return Log|null Log or null if there is no log
	 */
	public function getOldest(): ?Log {
		if ( empty( $this->logs ) ) {
			return null;
		}
		return $this->logs[ min( array_keys( $this->logs ) ) ];
	}

	public function isEmpty(): bool {
		return empty( $this->logs );
	}

	/**
	 *
	 * @throws RuntimeException
	 */
	public function get( int $id ): Log {
		if ( $this->hasLogWithId( $id ) ) {
			return $this->logs[$id];
		}
		throw new RuntimeException( 'No such Log loaded in LogList object' );
	}

	/**
	 * @return Log[]
	 */
	public function toArray(): array {
		return $this->logs;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return Log[]
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	public static function jsonDeserialize( array $json ): LogList {
		$self = new LogList();
		foreach ( $json as $logJson ) {
			$self->addLog( Log::jsonDeserialize( $logJson ) );
		}
		return $self;
	}
}
