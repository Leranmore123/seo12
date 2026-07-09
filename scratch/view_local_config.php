<?php
header('Content-Type: text/plain');

$localFile = dirname(__DIR__) . '/config.local.php';
if (file_exists($localFile) && is_readable($localFile)) {
    $config = include $localFile;
    echo "=== LOCAL CONFIG OVERRIDES ===\n";
    if (is_array($config)) {
        foreach ($config as $key => $val) {
            if (stripos($key, 'pass') !== false || stripos($key, 'key') !== false || stripos($key, 'secret') !== false) {
                echo "$key: [HIDDEN]\n";
            } else {
                echo "$key: " . var_export($val, true) . "\n";
            }
        }
    } else {
        echo "Local config did not return an array.\n";
    }
} else {
    echo "config.local.php does not exist or is not readable.\n";
}
