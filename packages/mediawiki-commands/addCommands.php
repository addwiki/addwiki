<?php

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new \Addwiki\Commands\Mediawiki\EditPage( $awwConfig ),
		new \Addwiki\Commands\Mediawiki\Purge( $awwConfig ),
		new \Addwiki\Commands\Mediawiki\RestoreRevisions( $awwConfig ),
	];
};
