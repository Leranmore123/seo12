<?php
require_once __DIR__ . '/../config.php';

$tok = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$sec = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "=== Base64 Decoding Check ===\n";
echo "Raw Token: '{$tok}' (len: " . strlen($tok) . ")\n";
echo "Base64 Decoded Token: '" . base64_decode($tok) . "'\n\n";

echo "Raw Token Secret: '{$sec}' (len: " . strlen($sec) . ")\n";
echo "Base64 Decoded Token Secret: '" . base64_decode($sec) . "'\n";
