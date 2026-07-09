<?php
header('Content-Type: text/plain');

$targetFile = dirname(__DIR__) . '/submission-manager.php';
echo "Checking: $targetFile\n\n";

$output = [];
$return_var = 0;
exec('php -l ' . escapeshellarg($targetFile) . ' 2>&1', $output, $return_var);

echo "Lint Exit Code: $return_var\n";
echo "Output:\n";
echo implode("\n", $output);

echo "\n\nChecking config.php:\n";
$output2 = [];
exec('php -l ' . escapeshellarg(dirname(__DIR__) . '/config.php') . ' 2>&1', $output2, $return_var2);
echo "Lint Exit Code: $return_var2\n";
echo "Output:\n";
echo implode("\n", $output2);
