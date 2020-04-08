<?php

// This could probably be written in bash to avoid needing php...

define( 'DIR_ROOT', realpath( __DIR__ . DIRECTORY_SEPARATOR . '..' ) );
define( 'DIR_PACKAGES', DIR_ROOT . DIRECTORY_SEPARATOR . 'packages' );

require_once DIR_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function getPackageNames(): array {
	$packageDirectories =
		array_filter( glob( DIR_PACKAGES . DIRECTORY_SEPARATOR . '*' ), 'is_dir' );

	return array_map( 'basename', $packageDirectories );
}

function getComposerJsonPath( $package ) {
	return DIR_PACKAGES . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'composer.json';
}

function getComposerJsonData( $package ) {
	return json_decode( file_get_contents( getComposerJsonPath( $package ) ), true );
}

function getCurrentStatedVersion( string $package ) {
	$data = getComposerJsonData( $package );

	return array_key_exists( 'version', $data ) ? $data['version'] : null;
}

function getComposerLockData() {
	return json_decode(
		file_get_contents( DIR_ROOT . DIRECTORY_SEPARATOR . 'composer.lock' ),
		true
	);
}

/**
 * @param string[] $packages add wiki package name such as "mediawiki-api"
 * @return string[] of composer package names that are not installed from "path"
 */
function getPackagesNotInstalledFromPath( $packages ) {
	array_walk( $packages, function ( &$element ) { $element = 'addwiki/' . $element; } );
	$data = getComposerLockData();
	$notOkay = [];
	foreach ( $data['packages'] as $packageData ) {
		if ( !in_array( $packageData['name'], $packages ) ) {
			continue;
		}
		if ( $packageData['dist']['type'] !== 'path' ) {
			$notOkay[] = $packageData['name'];
		}
	}

	return $notOkay;
}

/**
 * @param string[] $packages add wiki package name such as "mediawiki-api"
 * @return string[] of composer package names that do not have a version defined in composer.json
 */
function getPackagesWithoutComposerJsonVersion( $packages ) {
	$notOkay = [];
	foreach ( $packages as $package ) {
		if( getCurrentStatedVersion( $package ) === null ) {
			$notOkay[] = 'addwiki/' . $package;
		}
	}
	return $notOkay;
}

// Begin real processing
$c = new Colors\Color();
$c->setUserStyles(
	[
		'okay' => 'green',
		'error' => 'red',
	]
);
$packages = getPackageNames();
$exitCode = 0;

// Make sure everything states a version
$withoutComposerJsonVersion = getPackagesWithoutComposerJsonVersion( $packages );
if ( $withoutComposerJsonVersion ) {
	$exitCode = 1;
	echo $c( "ERROR: Some packages do not state their version. FIXME!\n" )->error;
	echo $c( " - " . implode( ', ', $withoutComposerJsonVersion ) . "\n" )->error;
} else {
	echo $c( "All packages correctly state their version\n" )->okay;
}

// Make sure everything is installed from "path"
$notInstalledFromPath = getPackagesNotInstalledFromPath( $packages );
if ( $notInstalledFromPath ) {
	$exitCode = 1;
	echo $c( "ERROR: Some packages are not installed from 'path'. FIXME!\n" )->error;
	echo $c( " - " . implode( ', ', $notInstalledFromPath ) . "\n" )->error;
} else {
	echo $c( "All packages are correctly installed from 'path'\n" )->okay;
}

exit( $exitCode );
