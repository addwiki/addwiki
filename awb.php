<?php

require __DIR__ . '/vendor/autoload.php';

use Mediawiki\Bot\Commands\CleanSandbox;
use Mediawiki\Bot\Commands\ListWikis;
use Mediawiki\Bot\Commands\Setup;
use Mediawiki\Bot\Config\AppConfig;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

$awbConfig = new AppConfig( __DIR__  );
$awbApp = new Application( 'awb - addwiki bot' );

$awbApp->addCommands( array(
	new Setup( $awbConfig ),
	new ListWikis( $awbConfig ),
	new CleanSandbox( $awbConfig ),
) );

if( $awbConfig->isEmpty() ) {
	$awbApp->setDefaultCommand( 'setup' );
}

$awbApp->run();
