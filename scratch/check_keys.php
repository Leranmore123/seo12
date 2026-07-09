<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

echo "=== AWS SERVER KEYS CHECK ===\n";
echo "GOOGLE_API_KEY: '" . GOOGLE_API_KEY . "'\n";
echo "GOOGLE_CSE_CX: '" . GOOGLE_CSE_CX . "'\n";
echo "Is empty: " . (empty(GOOGLE_API_KEY) ? "YES" : "NO") . "\n";
