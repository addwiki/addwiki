<?php

require __DIR__ . '/vendor/autoload.php';

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

if( $awbConfig->isEmpty() ) {
	$awbApp->setDefaultCommand( 'config:setup' );
}

$awbApp->run();
