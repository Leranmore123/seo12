<?php
$file = dirname(__DIR__) . '/selenium/pinterest_error.png';
if (file_exists($file)) {
    echo "File exists!\n";
    echo "Owner ID: " . fileowner($file) . "\n";
    if (function_exists('posix_getpwuid')) {
        echo "Owner: " . posix_getpwuid(fileowner($file))['name'] . "\n";
    }
    echo "Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "\n";
    echo "Last modified: " . date("Y-m-d H:i:s", filemtime($file)) . " UTC\n";
    echo "Current time: " . date("Y-m-d H:i:s") . " UTC\n";
} else {
    echo "File does not exist!\n";
}
