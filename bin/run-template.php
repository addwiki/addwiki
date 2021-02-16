<?php

declare(strict_types=1);





/**
 * Run a template command.
 * Examples:
 *    bin/run-template vendor/bin/parallel-lint DIR --
 *    bin/run-template vendor/bin/parallel-lint DIR -- all
 *    bin/run-template vendor/bin/parallel-lint DIR -- packages/mediawiki-api-base
 */

$splitKey = array_search('--', $argv);
$templateCommand = implode( ' ', array_slice($argv,1,$splitKey-1) );
$directory = implode( ' ', array_slice($argv,$splitKey+1,1) );

if($directory === '') {
    $directory = 'all';
} elseif((is_array($argv) || $argv instanceof Countable ? count($argv) : 0) !== $splitKey +2) {
    die("You're using it wrong!");
}

$dirs = [];
if($directory === 'all') {
    $glob = array_merge(
        array_filter(glob(__DIR__ . '/../packages/*'), 'is_dir'),
        array_filter(glob(__DIR__ . '/../packages-dev/*'), 'is_dir')
    );
    foreach( $glob as $dir ) {
        $relativeDir = str_replace(realpath(__DIR__."/../"),'.',realpath($dir));
        $dirs[] = $relativeDir;
    }
} else {
    $dirs[] = $directory;
}

$finalExitCode = 0;
foreach( $dirs as $dir ) {
    $command = str_replace('DIR', $dir, $templateCommand);
    echo "\033[0;32m" . "Running: " . "\033[0;36m" . $command . "\033[0m" . PHP_EOL;
    $exitCode = runAndStreamCommand( $command, realpath(__DIR__ . '/../') );
    echo "\033[0;32m" . "Exit code: " . "\033[0;36m" . $exitCode . "\033[0m" . PHP_EOL;
    if($exitCode != 0) {
        $finalExitCode = $exitCode;
    }
}

function runAndStreamCommand( $cmd, $cwd ) {
    $descriptorspec = array(
        0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
        2 => array("pipe", "w")    // stderr is a pipe that the child will write to
     );
     flush();
     $process = proc_open($cmd, $descriptorspec, $pipes, $cwd);
     if (is_resource($process)) {
         while ( ( $c = fgetc($pipes[1]) ) !== false ) {
             echo $c;
             flush();
         }
     }
     flush();
     return proc_get_status($process)['exitcode'];
}

// Exit had to be moved below function definition due to https://github.com/rectorphp/rector/issues/5571
exit( $finalExitCode );
