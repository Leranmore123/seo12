<?php
header('Content-Type: text/plain');

$logPath = ini_get('error_log');
echo "PHP Error Log Path: " . ($logPath ? $logPath : "Not configured in php.ini") . "\n\n";

if ($logPath && file_exists($logPath) && is_readable($logPath)) {
    echo "=== LAST 50 PHP ERROR LOG LINES ===\n";
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    echo implode("", $lastLines);
} else {
    echo "Error log file does not exist, is not readable, or is not configured.\n";
    
    // Let's try standard system paths
    $commonLogs = [
        '/var/log/apache2/error.log', // Apache error log
        '/var/log/nginx/error.log',
        '/var/log/php-fpm.log',
        '/var/log/php7.4-fpm.log',
        '/var/log/php8.1-fpm.log',
        '/var/log/php8.2-fpm.log',
        '/var/log/php8.3-fpm.log'
    ];
    
    foreach ($commonLogs as $log) {
        if (file_exists($log) && is_readable($log)) {
            echo "\n=== READING COMMON LOG: $log ===\n";
            $lines = file($log);
            $lastLines = array_slice($lines, -50);
            echo implode("", $lastLines);
            break;
        } else {
            echo "Cannot read: $log\n";
        }
    }
}
