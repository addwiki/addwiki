<?php

use Addwiki\Commands\Wikimedia\ExtensionToWikidata;
use Addwiki\Commands\Wikimedia\WikidataReferenceDateFixer;
use Addwiki\Commands\Wikimedia\WikidataReferencer\WikidataReferencerCommand;
use Addwiki\Commands\Wikimedia\WikidataCovid19\ImportWHOReportValueCommand;
$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return [
		new ExtensionToWikidata( $awwConfig ),
		new WikidataReferenceDateFixer( $awwConfig ),
		new WikidataReferencerCommand( $awwConfig ),
		new ImportWHOReportValueCommand( $awwConfig ),
	];
};
