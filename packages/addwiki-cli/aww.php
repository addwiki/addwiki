<?php

use Addwiki\Commands\Config\ConfigList;
use Addwiki\Commands\Config\SetDefaultUser;
use Addwiki\Commands\Config\SetDefaultWiki;
use Addwiki\Commands\Config\Setup;
use Addwiki\Config\AppConfig;
use Symfony\Component\Console\Application;

/**
 * Per https://github.com/guzzle/guzzle/blob/master/docs/faq.rst
 * "Maximum function nesting level of '100' reached, aborting" is possible
 * This error message comes specifically from the XDebug extension.
 */
ini_set( 'xdebug.max_nesting_level', 1000 );

// global variable to allow registering additional commands
// each callback should take a single parameter implementing ArrayAccess (the app config)
$GLOBALS['awwCommands'] = [];
if ( file_exists( __DIR__ . '/../../autoload.php' ) ) {
	// addwiki is part of a composer installation
	require_once __DIR__ . '/../../autoload.php';
} elseif ( file_exists( __DIR__ . '/../../vendor/autoload.php' ) ) {
	// addwiki is part of the mono repo and symlinked..?
	require_once __DIR__ . '/../../vendor/autoload.php';
} else {
	// addwiki is just compser installed as itself?
	require_once __DIR__ . '/vendor/autoload.php';
}

$awwConfig = new AppConfig( __DIR__ );
$awwApp = new Application( 'aww - addwiki cli tool' );

$awwApp->addCommands( [
	new Setup( $awwConfig ),
	new ConfigList( $awwConfig ),
	new SetDefaultWiki( $awwConfig ),
	new SetDefaultUser( $awwConfig ),
] );

foreach ( $GLOBALS['awwCommands'] as $callback ) {
	if ( is_callable( $callback ) ) {
		$awwApp->addCommands( call_user_func( $callback, $awwConfig ) );
	}
}

$awwApp->run();
