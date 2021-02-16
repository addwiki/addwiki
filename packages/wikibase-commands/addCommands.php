<?php

use Addwiki\Commands\Wikibase\WikibaseEntityStatementRemover;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new WikibaseEntityStatementRemover( $awwConfig ),
	];
};
