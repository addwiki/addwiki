<?php

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new \Addwiki\Commands\Wikibase\WikibaseEntityStatementRemover( $awwConfig ),
	];
};
