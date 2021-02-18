<?php

namespace Addwiki\Mediawiki\Api;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Service\CategoryTraverser;
use Addwiki\Mediawiki\Api\Service\FileUploader;
use Addwiki\Mediawiki\Api\Service\ImageRotator;
use Addwiki\Mediawiki\Api\Service\LogListGetter;
use Addwiki\Mediawiki\Api\Service\NamespaceGetter;
use Addwiki\Mediawiki\Api\Service\PageDeleter;
use Addwiki\Mediawiki\Api\Service\PageGetter;
use Addwiki\Mediawiki\Api\Service\PageListGetter;
use Addwiki\Mediawiki\Api\Service\PageMover;
use Addwiki\Mediawiki\Api\Service\PageProtector;
use Addwiki\Mediawiki\Api\Service\PagePurger;
use Addwiki\Mediawiki\Api\Service\PageRestorer;
use Addwiki\Mediawiki\Api\Service\PageWatcher;
use Addwiki\Mediawiki\Api\Service\Parser;
use Addwiki\Mediawiki\Api\Service\RevisionDeleter;
use Addwiki\Mediawiki\Api\Service\RevisionPatroller;
use Addwiki\Mediawiki\Api\Service\RevisionRestorer;
use Addwiki\Mediawiki\Api\Service\RevisionRollbacker;
use Addwiki\Mediawiki\Api\Service\RevisionSaver;
use Addwiki\Mediawiki\Api\Service\RevisionUndoer;
use Addwiki\Mediawiki\Api\Service\UserBlocker;
use Addwiki\Mediawiki\Api\Service\UserCreator;
use Addwiki\Mediawiki\Api\Service\UserGetter;
use Addwiki\Mediawiki\Api\Service\UserRightsChanger;

/**
 * @access public
 *
 * @author Addshore
 */
class MediawikiFactory {

	private \Addwiki\Mediawiki\Api\Client\MediawikiApi $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * Get a new CategoryTraverser object for this API.
	 */
	public function newCategoryTraverser(): CategoryTraverser {
		return new CategoryTraverser( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newRevisionSaver(): RevisionSaver {
		return new RevisionSaver( $this->api );
	}

	/**
	 * @since 0.5
	 */
	public function newRevisionUndoer(): RevisionUndoer {
		return new RevisionUndoer( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageGetter(): PageGetter {
		return new PageGetter( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newUserGetter(): UserGetter {
		return new UserGetter( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageDeleter(): PageDeleter {
		return new PageDeleter( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageMover(): PageMover {
		return new PageMover( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageListGetter(): PageListGetter {
		return new PageListGetter( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageRestorer(): PageRestorer {
		return new PageRestorer( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPagePurger(): PagePurger {
		return new PagePurger( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newRevisionRollbacker(): RevisionRollbacker {
		return new RevisionRollbacker( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newRevisionPatroller(): RevisionPatroller {
		return new RevisionPatroller( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newPageProtector(): PageProtector {
		return new PageProtector( $this->api );
	}

	/**
	 * @since 0.5
	 */
	public function newPageWatcher(): PageWatcher {
		return new PageWatcher( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newRevisionDeleter(): RevisionDeleter {
		return new RevisionDeleter( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newRevisionRestorer(): RevisionRestorer {
		return new RevisionRestorer( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newUserBlocker(): UserBlocker {
		return new UserBlocker( $this->api );
	}

	/**
	 * @since 0.3
	 */
	public function newUserRightsChanger(): UserRightsChanger {
		return new UserRightsChanger( $this->api );
	}

	/**
	 * @since 0.5
	 */
	public function newUserCreator(): UserCreator {
		return new UserCreator( $this->api );
	}

	/**
	 * @since 0.4
	 */
	public function newLogListGetter(): LogListGetter {
		return new LogListGetter( $this->api );
	}

	/**
	 * @since 0.5
	 */
	public function newFileUploader(): FileUploader {
		return new FileUploader( $this->api );
	}

	/**
	 * @since 0.5
	 */
	public function newImageRotator(): ImageRotator {
		return new ImageRotator( $this->api );
	}

	/**
	 * @since 0.6
	 */
	public function newParser(): Parser {
		return new Parser( $this->api );
	}

	/**
	 * @since 0.7
	 */
	public function newNamespaceGetter(): NamespaceGetter {
		return new NamespaceGetter( $this->api );
	}
}
