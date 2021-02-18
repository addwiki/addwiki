<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;

class Page {

	private Revisions $revisions;

	private ?PageIdentifier $pageIdentifier;

	/**
	 * @param PageIdentifier|null $pageIdentifier
	 * @param Revisions|null $revisions
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( PageIdentifier $pageIdentifier = null, Revisions $revisions = null ) {
		if ( $revisions === null ) {
			$revisions = new Revisions();
		}
		$this->revisions = $revisions;
		$this->pageIdentifier = $pageIdentifier;
	}

	/**
	 * @deprecated since 0.5
	 */
	public function getId(): ?int {
		return $this->pageIdentifier->getId();
	}

	public function getRevisions(): Revisions {
		return $this->revisions;
	}

	/**
	 * @deprecated since 0.5
	 */
	public function getTitle(): ?Title {
		return $this->pageIdentifier->getTitle();
	}

	public function getPageIdentifier(): ?PageIdentifier {
		return $this->pageIdentifier;
	}

}
