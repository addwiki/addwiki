<?php

$GLOBALS['awwCommands'][] = function ( $awwConfig ) {
	return array(
		new \Addwiki\Commands\Wikimedia\ExtensionToWikidata( $awwConfig ),
		new \Addwiki\Commands\Wikimedia\WikidataReferenceDateFixer( $awwConfig ),
		new \Addwiki\Commands\Wikimedia\WikidataReferencer\WikidataReferencerCommand( $awwConfig ),
		new \Addwiki\Commands\Wikimedia\WikidataCovid19\ImportWHOReportValueCommand( $awwConfig ),
	);
};