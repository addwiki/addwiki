<?php

declare(strict_types=1);

/**
 * Copies the mono repo autoload.php into the vendor directory of each package.
 * This means that scripts and tests and run in those packages using the correct wider packages.
 * Assumptions about autoload.php existing by tools such as phpunit also hold true.
 */

$dirs = array_merge(
    array_filter(glob(__DIR__ . '/../packages/*'), 'is_dir'),
    array_filter(glob(__DIR__ . '/../packages-dev/*'), 'is_dir')
);

foreach( $dirs as $dir ) {
    // Make a vendor dir
    $vendorDir = $dir . "/vendor";
    if(!is_dir($vendorDir)){
        mkdir($vendorDir, 0755);
    }

    // Make a stub autoload file
    $autoloadPath = $vendorDir . "/autoload.php";
    file_put_contents( $autoloadPath, "<?php\nrequire_once(__DIR__ . '/../../../vendor/autoload.php');" );
}


function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}