<?php
header('Content-Type: text/plain');

echo "=== AWS PHP ERROR LOG CHECK ===\n\n";

// 1. Check Apache error log
$apacheLog = '/var/log/apache2/error.log';
if (is_readable($apacheLog)) {
    echo "--- Apache Error Log ($apacheLog) Last 30 Lines ---\n";
    $lines = file($apacheLog);
    $lastLines = array_slice($lines, -30);
    echo implode("", $lastLines);
} else {
    echo "Apache error log ($apacheLog) is not directly readable. Let's try via tail shell exec:\n";
    $output = [];
    $retval = 0;
    exec('tail -n 30 /var/log/apache2/error.log 2>&1', $output, $retval);
    echo implode("\n", $output) . "\n";
}

echo "\n\n--- Project-level PHP Error Logs ---\n";
$localLogs = ['error_log', 'php_error.log', 'error.log'];
$found = false;
foreach ($localLogs as $logFile) {
    $fullPath = dirname(__DIR__) . '/' . $logFile;
    if (file_exists($fullPath)) {
        $found = true;
        echo "\nFound local log: $logFile\n";
        $lines = file($fullPath);
        $lastLines = array_slice($lines, -30);
        echo implode("", $lastLines);
    }
}
if (!$found) {
    echo "No local PHP error log files found in the project root.\n";
}
