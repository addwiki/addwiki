<?php

use Addwiki\Mediawiki\Commands\EditPage;
use Addwiki\Mediawiki\Commands\Purge;
use Addwiki\Mediawiki\Commands\RestoreRevisions;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new EditPage( $awwConfig ),
		new Purge( $awwConfig ),
		new RestoreRevisions( $awwConfig ),
	];
};
