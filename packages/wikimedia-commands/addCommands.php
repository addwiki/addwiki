<?php

use Addwiki\Wikimedia\Commands\ExtensionToWikidata;
use Addwiki\Wikimedia\Commands\WikidataReferenceDateFixer;
use Addwiki\Wikimedia\Commands\WikidataReferencer\WikidataReferencerCommand;

$GLOBALS['awwCommands'][] = fn( $awwConfig ) => [
		new ExtensionToWikidata( $awwConfig ),
		new WikidataReferenceDateFixer( $awwConfig ),
		new WikidataReferencerCommand( $awwConfig ),
	];
