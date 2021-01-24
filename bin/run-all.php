<?php

declare(strict_types=1);

/**
 * Run a templated command for every package
 *
 * Example: bin/run-all vendor/bin/parallel-lint DIR --exclude DIR/vendor
 *
 * The command will run with the CWD of the package directory
 * DIR in the template will be replaced by the package directory
 * The command that is being run must be in the monorepo directory, as it will be run from there
 */

$template = implode(" ", array_slice($argv, 1, count($argv)-1, true));

$dirs = array_merge(
    array_filter(glob(__DIR__ . '/../packages/*'), 'is_dir'),
    array_filter(glob(__DIR__ . '/../packages-dev/*'), 'is_dir')
);

foreach( $dirs as $dir ) {
    $cmd = "./../../" . str_replace("DIR", $dir, $template);
    $displayDir = str_replace(realpath(__DIR__."/../"),'.',realpath($dir));
    echo "\033[0;32m" . "## Running " . "\033[0;31m" . $template . "\033[0;32m" . " for directory: " . "\033[0;36m" . $displayDir . "\033[0m" . PHP_EOL;
    runAndStreamCommand( $cmd, $dir );
}

function runAndStreamCommand( $cmd, $cwd ) {
    $descriptorspec = array(
        0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
        2 => array("pipe", "w")    // stderr is a pipe that the child will write to
     );
     flush();
     $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, array());
     if (is_resource($process)) {
         while ( ( $c = fgetc($pipes[1]) ) !== false ) {
             echo $c;
             flush();
         }
     }
     echo PHP_EOL;
}