<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM social_accounts WHERE id = 4987");
$acc = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== Account #4987 DB Var Dump ===\n";
var_dump($acc['username']);
var_dump($acc['api_key']);
var_dump($acc['api_secret']);
var_dump($acc['password']);

$rawPass = $acc['password'] ?? '';
if (strpos($rawPass, ':') !== false) {
    $decrypted = $rawPass;
} else {
    $decrypted = base64_decode($rawPass, true);
    if ($decrypted === false || strpos($decrypted, ':') === false) {
        $decrypted = $rawPass;
    }
}
$parts = explode(':', $decrypted);
$oauthToken = trim($parts[0] ?? '');
$oauthTokenSecret = trim($parts[1] ?? '');

echo "\nExtracted OAuth Token: '$oauthToken' (len: " . strlen($oauthToken) . ")\n";
echo "Extracted OAuth Token Secret: '$oauthTokenSecret' (len: " . strlen($oauthTokenSecret) . ")\n";

$hardToken = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$hardSecret = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "Hardcoded Token Match: " . ($oauthToken === $hardToken ? 'YES' : 'NO') . "\n";
echo "Hardcoded Token Secret Match: " . ($oauthTokenSecret === $hardSecret ? 'YES' : 'NO') . "\n";

echo "\n=== Testing postToTumblr with Account #4987 ===\n";
$res = postToTumblr($acc, "villa for sale Vadodara", "https://propertysdeal.in/propertys-details/villa-for-sale-vadodara", GEMINI_API_KEY, OPENAI_API_KEY, 1, [], 252);
print_r($res);
