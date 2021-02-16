<?php

use Addwiki\Commands\Wikimedia\ExtensionToWikidata;
use Addwiki\Commands\Wikimedia\WikidataCovid19\ImportWHOReportValueCommand;
use Addwiki\Commands\Wikimedia\WikidataReferenceDateFixer;
use Addwiki\Commands\Wikimedia\WikidataReferencer\WikidataReferencerCommand;

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new ExtensionToWikidata( $awwConfig ),
		new WikidataReferenceDateFixer( $awwConfig ),
		new WikidataReferencerCommand( $awwConfig ),
		new ImportWHOReportValueCommand( $awwConfig ),
	];
};
