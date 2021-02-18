<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * Represents flags that can be used when edits are made
 */
class EditInfo {

	public const MINOR = true;
	public const NOTMINOR = false;

	public const BOT = true;
	public const NOTBOT = false;

	public const OFFLAG = null;

	protected bool $minor = false;
	protected bool $bot = false;
	protected ?int $maxlag;
	protected string $summary;

	public function __construct( string $summary = '', bool $minor = self::NOTMINOR, bool $bot = self::NOTBOT, ?int $maxlag = self::OFFLAG ) {
		if ( !is_string( $summary ) ) {
			throw new InvalidArgumentException( '$summary must be a string' );
		}
		if ( !is_bool( $minor ) ) {
			throw new InvalidArgumentException( '$minor must be a bool' );
		}
		if ( !is_bool( $bot ) ) {
			throw new InvalidArgumentException( '$bot must be a bool' );
		}
		if ( !is_int( $maxlag ) && $maxlag !== null ) {
			throw new InvalidArgumentException( '$maxlag must be an integer or null' );
		}
		$this->summary = $summary;
		$this->bot = $bot;
		$this->minor = $minor;
		$this->maxlag = $maxlag;
	}

	/**
	 * @return EditInfo::BOT|EditInfo::NOTBOT
	 */
	public function getBot(): bool {
		return $this->bot;
	}

	/**
	 * @return EditInfo::MINOR|EditInfo::NOTMINOR
	 */
	public function getMinor(): bool {
		return $this->minor;
	}

	/**
	 * @return int|null
	 */
	public function getMaxlag(): ?int {
		return $this->maxlag;
	}

	public function getSummary(): string {
		return $this->summary;
	}

}
