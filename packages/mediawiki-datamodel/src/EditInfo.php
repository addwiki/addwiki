<?php

namespace Addwiki\Mediawiki\DataModel;

/**
 * Represents flags that can be used when edits are made
 */
class EditInfo {

	/**
	 * @var bool
	 */
	public const MINOR = true;
	/**
	 * @var bool
	 */
	public const NOTMINOR = false;

	/**
	 * @var bool
	 */
	public const BOT = true;
	/**
	 * @var bool
	 */
	public const NOTBOT = false;

	/**
	 * @var null
	 */
	public const OFFLAG = null;

	protected bool $minor = false;
	protected bool $bot = false;
	protected ?int $maxlag;
	protected string $summary;

	public function __construct( string $summary = '', bool $minor = self::NOTMINOR, bool $bot = self::NOTBOT, ?int $maxlag = self::OFFLAG ) {
		$this->summary = $summary;
		$this->bot = $bot;
		$this->minor = $minor;
		$this->maxlag = $maxlag;
	}

	public function getBot(): bool {
		return $this->bot;
	}

	public function getMinor(): bool {
		return $this->minor;
	}

	public function getMaxlag(): ?int {
		return $this->maxlag;
	}

	public function getSummary(): string {
		return $this->summary;
	}

}
