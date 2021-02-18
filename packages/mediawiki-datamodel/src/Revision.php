<?php

namespace Addwiki\Mediawiki\DataModel;

/**
 * Representation of a version of content
 *
 * @author Addshore
 */
class Revision {

	/**
	 * @var int Id of the revision
	 */
	private ?int $id;

	/**
	 * @var PageIdentifier of the page for the revision
	 */
	private \Addwiki\Mediawiki\DataModel\PageIdentifier $pageIdentifier;

	private Content $content;

	private EditInfo $editInfo;

	private ?string $user;

	private ?string $timestamp;

	/**
	 * @param PageIdentifier|null $pageIdentifier
	 * @param EditInfo|null $editInfo
	 */
	public function __construct(
		Content $content,
		PageIdentifier $pageIdentifier = null,
		?int $revId = null,
		EditInfo $editInfo = null,
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
		$this->id = $revId;
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

	/**
	 * @return int|null
	 */
	public function getId(): int {
		return $this->id;
	}

	public function getPageIdentifier(): \Addwiki\Mediawiki\DataModel\PageIdentifier {
		return $this->pageIdentifier;
	}

	/**
	 * @return null|string
	 */
	public function getUser(): ?string {
		return $this->user;
	}

	/**
	 * @return null|string
	 */
	public function getTimestamp(): ?string {
		return $this->timestamp;
	}

}
