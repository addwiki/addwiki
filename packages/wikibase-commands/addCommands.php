<?php

use Addwiki\Wikibase\Commands\WikibaseEntityStatementRemover;

$GLOBALS['awwCommands'][] = fn( $awwConfig ) => [
		new WikibaseEntityStatementRemover( $awwConfig ),
	];
