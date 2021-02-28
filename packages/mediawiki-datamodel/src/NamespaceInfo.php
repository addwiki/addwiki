<?php

namespace Addwiki\Mediawiki\DataModel;

/**
 * Represents metadata about a MediaWiki namespace
 */
class NamespaceInfo {

	private int $id;
	private string $canonicalName;
	private string $localName;
	private string $caseHandling;
	private ?string $defaultContentModel;
	private array $aliases = [];

	public function __construct( int $id, string $canonicalName, string $localName, string $caseHandling, ?string $defaultContentModel = null, array $aliases = [] ) {
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
