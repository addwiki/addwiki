<?php

// global variable to allow registering additional commands
$GLOBALS['awbCommands'] = array();

if( file_exists( __DIR__ . '/../../autoload.php' ) ) {
    // addwiki is part of a composer installation
    require_once __DIR__ . '/../../autoload.php';
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}

$awbConfig = new Mediawiki\Bot\Config\AppConfig( __DIR__  );
$awbApp = new Symfony\Component\Console\Application( 'awb - addwiki bot' );

$awbApp->addCommands( array(
	new Mediawiki\Bot\Commands\Config\Setup( $awbConfig ),
	new Mediawiki\Bot\Commands\Config\ConfigList( $awbConfig ),
	new Mediawiki\Bot\Commands\Config\SetDefaultWiki( $awbConfig ),
	new Mediawiki\Bot\Commands\Config\SetDefaultUser( $awbConfig ),
	new Mediawiki\Bot\Commands\Task\RestoreRevisions( $awbConfig ),
	new Mediawiki\Bot\Commands\Task\Purge( $awbConfig ),
) );

$awbApp->addCommands( $GLOBALS['awbCommands'] );

if( $awbConfig->isEmpty() ) {
	$awbApp->setDefaultCommand( 'config:setup' );
}

$awbApp->run();
