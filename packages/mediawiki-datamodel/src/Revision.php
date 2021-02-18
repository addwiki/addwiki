<?php

namespace Addwiki\Mediawiki\DataModel;

/**
 * Representation of a version of content
 */
class Revision {

	private Content $content;
	private PageIdentifier $pageIdentifier;
	private ?int $id;
	private EditInfo $editInfo;
	private ?string $user;
	private ?string $timestamp;

	public function __construct(
		Content $content,
		?PageIdentifier $pageIdentifier = null,
		?int $id = null,
		?EditInfo $editInfo = null,
		?string $user = null,
		?string $timestamp = null
		) {
		if ( $editInfo === null ) {
			$editInfo = new EditInfo();
		}
		if ( $pageIdentifier === null ) {
			$pageIdentifier = new PageIdentifier();
		}
		$this->content = $content;
		$this->pageIdentifier = $pageIdentifier;
		$this->id = $id;
		$this->editInfo = $editInfo;
		$this->user = $user;
		$this->timestamp = $timestamp;
	}

	public function getContent(): Content {
		return $this->content;
	}

	public function getEditInfo(): EditInfo {
		return $this->editInfo;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getPageIdentifier(): PageIdentifier {
		return $this->pageIdentifier;
	}

	public function getUser(): ?string {
		return $this->user;
	}

	public function getTimestamp(): ?string {
		return $this->timestamp;
	}

}
