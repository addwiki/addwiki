<?php

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return array(
		new \Addwiki\Commands\Wikibase\WikibaseEntityStatementRemover( $awwConfig ),
	);
};
