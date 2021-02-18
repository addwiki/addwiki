<?php

use Addwiki\Mediawiki\Commands\EditPage;
use Addwiki\Mediawiki\Commands\Purge;
use Addwiki\Mediawiki\Commands\RestoreRevisions;

$GLOBALS['awwCommands'][] = fn( $awwConfig ) => [
		new EditPage( $awwConfig ),
		new Purge( $awwConfig ),
		new RestoreRevisions( $awwConfig ),
	];
