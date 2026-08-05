<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM social_accounts WHERE platform='tumblr' ORDER BY id DESC LIMIT 1");
$acc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$acc) {
    die("No tumblr account found in DB\n");
}

echo "=== Tumblr Account DB Info ===\n";
echo "ID: " . $acc['id'] . "\n";
echo "Username: " . $acc['username'] . "\n";
echo "API Key: " . $acc['api_key'] . "\n";
echo "API Secret: " . $acc['api_secret'] . "\n";
echo "Password Raw: " . $acc['password'] . "\n";

$rawPass = $acc['password'] ?? '';
$decrypted = base64_decode($rawPass, true);
if ($decrypted === false || strpos($decrypted, ':') === false) {
    $decrypted = $rawPass;
}
$parts = explode(':', $decrypted);
$token = trim($parts[0] ?? '');
$secret = trim($parts[1] ?? '');

echo "Extracted Token: " . $token . "\n";
echo "Extracted Token Secret: " . $secret . "\n";

// Test 1: Tumblr User Info Endpoint
$url = "https://api.tumblr.com/v2/user/info";
$authHeader = getTumblrOAuthHeader($acc['api_key'], $acc['api_secret'], $token, $secret, $url, 'GET', []);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [$authHeader],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n=== Tumblr /v2/user/info Test ===\n";
echo "HTTP Code: $code\n";
echo "Response: $resp\n";
