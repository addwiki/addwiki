<?php

use Addwiki\Wikibase\Commands\WikibaseEntityStatementRemover;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new WikibaseEntityStatementRemover( $awwConfig ),
	];
};
