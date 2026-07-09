<?php
header('Content-Type: text/plain');

$targetFile = dirname(__DIR__) . '/config.php';
echo "Checking config.php:\n";

$output = [];
$return_var = 0;
exec('php -l ' . escapeshellarg($targetFile) . ' 2>&1', $output, $return_var);

echo "Lint Exit Code: $return_var\n";
echo "Output:\n";
echo implode("\n", $output);
