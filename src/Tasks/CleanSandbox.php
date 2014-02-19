<?php

namespace Mediawiki\Bot\Tasks;

use Mediawiki\Api\Actions\Edit;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\Repos\PageRepo;
use Mediawiki\Api\Savers\EditSaver;
use Mediawiki\DataModel\EditFlags;

class CleanSandbox implements Task {

	/**
	 * @param MediawikiApi $api
	 * @param int $revId
	 * @param EditFlags $editFlags
	 */
	public function __construct( MediawikiApi $api, $revId, EditFlags $editFlags ) {
		$this->api = $api;
		$this->revid = $revId;
		$this->editFlags = $editFlags;
		$this->pageRepo = new PageRepo( $api );
		$this->editSaver = new EditSaver( $api );
	}

	/**
	 * @param bool $force
	 *
	 * @return string success
	 */
	public function run( $force = false ) {
		$page = $this->pageRepo->getFromRevisionId( $this->revid );
		$page = $this->pageRepo->getFromPage( $page );

		$good = $page->getRevisions()->get( $this->revid )->getContent();
		$current = $page->getRevisions()->getLatest()->getContent();

		if( $good === $current ) {
			return 'Sandbox already clear';
		}

		if( $force ) {
			$new = $good;
		} else if( strstr( $current, $good ) ) {
			$new = str_replace( $good, '', $current );
			//todo trim newlines from the top of new
			$new = $good . "\n\n" . $new;
		} else {
			$new = $good;
		}

		$edit = new Edit( $page, $new, $this->editFlags );
		$success = $this->editSaver->save( $edit );

		if( $success ) {
			return 'Cleared Sandbox';
		} else {
			return 'Failed to clear sandbox';
		}
	}
}