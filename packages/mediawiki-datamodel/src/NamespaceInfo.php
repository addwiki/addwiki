<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * Class representing metadata about a MediaWiki namespace
 *
 * @author gbirke
 */
class NamespaceInfo {
	private int $id;

	private string $canonicalName;

	private string $localName;

	private string $caseHandling;

	private ?string $defaultContentModel;

	private array $aliases = [];

	public function __construct( int $id, string $canonicalName, string $localName, string $caseHandling, ?string $defaultContentModel = null, array $aliases = [] ) {
		if ( !is_int( $id ) ) {
			throw new InvalidArgumentException( '$id must be an integer' );
		}
		if ( !is_string( $canonicalName ) ) {
			throw new InvalidArgumentException( '$canonicalName must be a string' );
		}
		if ( !is_string( $localName ) ) {
			throw new InvalidArgumentException( '$localName must be a string' );
		}
		if ( !is_string( $caseHandling ) ) {
			throw new InvalidArgumentException( '$caseHandling must be a string' );
		}
		if ( $defaultContentModel !== null && !is_string( $defaultContentModel ) ) {
			throw new InvalidArgumentException( '$canonicalName must be a string' );
		}

		if ( !is_array( $aliases ) ) {
			throw new InvalidArgumentException( '$aliases must be an array' );
		}

		$this->id = $id;
		$this->canonicalName = $canonicalName;
		$this->localName = $localName;
		$this->caseHandling = $caseHandling;
		$this->defaultContentModel = $defaultContentModel;
		$this->aliases = $aliases;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getCanonicalName(): string {
		return $this->canonicalName;
	}

	public function getLocalName(): string {
		return $this->localName;
	}

	public function getCaseHandling(): string {
		return $this->caseHandling;
	}

	public function getDefaultContentModel(): ?string {
		return $this->defaultContentModel;
	}

	/**
	 * @return mixed[]
	 */
	public function getAliases(): array {
		return $this->aliases;
	}

}
