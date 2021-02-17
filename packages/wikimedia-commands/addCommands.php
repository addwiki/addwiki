<?php

use Addwiki\Wikimedia\Commands\ExtensionToWikidata;
use Addwiki\Wikimedia\Commands\WikidataReferenceDateFixer;
use Addwiki\Wikimedia\Commands\WikidataReferencer\WikidataReferencerCommand;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new ExtensionToWikidata( $awwConfig ),
		new WikidataReferenceDateFixer( $awwConfig ),
		new WikidataReferencerCommand( $awwConfig ),
	];
};
