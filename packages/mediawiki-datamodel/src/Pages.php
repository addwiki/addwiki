<?php

namespace Addwiki\Mediawiki\DataModel;

use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a collection or Page classes
 *
 * @author Addshore
 */
class Pages {

	/**
	 * @var Page[]
	 */
	private array $pages = [];

	/**
	 * @param Page[] $pages
	 */
	public function __construct( array $pages = [] ) {
		$this->pages = [];
		$this->addPages( $pages );
	}

	/**
	 * @param Page[]|Pages $pages
	 *
	 * @throws InvalidArgumentException
	 */
	public function addPages( $pages ): void {
		if ( !is_array( $pages ) && !$pages instanceof Pages ) {
			throw new InvalidArgumentException( '$pages needs to either be an array or a Pages object' );
		}
		if ( $pages instanceof Pages ) {
			$pages = $pages->toArray();
		}
		foreach ( $pages as $page ) {
			$this->addPage( $page );
		}
	}

	/**
	 * @param Page $page
	 */
	public function addPage( Page $page ): void {
		$this->pages[$page->getId()] = $page;
	}

	public function hasPageWithId( int $id ): bool {
		return array_key_exists( $id, $this->pages );
	}

	/**
	 * @param Page $page
	 */
	public function hasPage( Page $page ): bool {
		return array_key_exists( $page->getId(), $this->pages );
	}

	/**
	 * @return Page|null Page or null if there is no page
	 */
	public function getLatest(): ?\Addwiki\Mediawiki\DataModel\Page {
		if ( empty( $this->pages ) ) {
			return null;
		}
		return $this->pages[ max( array_keys( $this->pages ) ) ];
	}

	/**
	 *
	 * @throws RuntimeException
	 */
	public function get( int $pageid ): \Addwiki\Mediawiki\DataModel\Page {
		if ( $this->hasPageWithId( $pageid ) ) {
			return $this->pages[$pageid];
		}
		throw new RuntimeException( 'No such page loaded in Pages object' );
	}

	/**
	 * @return Page[]
	 */
	public function toArray(): array {
		return $this->pages;
	}
}
