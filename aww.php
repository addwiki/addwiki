<?php

// global variable to allow registering additional commands
$GLOBALS['awwCommands'] = array();

if( file_exists( __DIR__ . '/../../autoload.php' ) ) {
    // addwiki is part of a composer installation
    require_once __DIR__ . '/../../autoload.php';
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}

$awwConfig = new Addwiki\Config\AppConfig( __DIR__  );
$awwApp = new Symfony\Component\Console\Application( 'aww - addwiki cli tool' );

$awwApp->addCommands( array(
	new Addwiki\Commands\Config\Setup( $awwConfig ),
	new Addwiki\Commands\Config\ConfigList( $awwConfig ),
	new Addwiki\Commands\Config\SetDefaultWiki( $awwConfig ),
	new Addwiki\Commands\Config\SetDefaultUser( $awwConfig ),
) );

foreach ( $GLOBALS['awwCommands'] as $callback ) {
	if ( is_callable( $callback ) ) {
		$awwApp->addCommands( call_user_func( $callback, $awwConfig ) );
	}
}

if( $awwConfig->isEmpty() ) {
	$awwApp->setDefaultCommand( 'config:setup' );
}

$awwApp->run();
