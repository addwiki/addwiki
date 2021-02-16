<?php

use Addwiki\Commands\Mediawiki\EditPage;
use Addwiki\Commands\Mediawiki\Purge;
use Addwiki\Commands\Mediawiki\RestoreRevisions;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new EditPage( $awwConfig ),
		new Purge( $awwConfig ),
		new RestoreRevisions( $awwConfig ),
	];
};
