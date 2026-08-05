<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$blog = 'flatforsalebopal.tumblr.com';
$consumerKey = 'jZJvMh6KxsS8iw7tMgbrCKXDBW9IOPm9GUx26vWvBajd5ikxUq';
$consumerSecret = 'Zxg1DOtbVMJgUpPDBnKwItPLfkI2112dfnmx4qvZNf6FmAbbei';
$oauthToken = 'D9TDCp4AKHvF7Xr1TFXWHzp3EmDch2ns0FmtLtiOnPca296RiE';
$oauthTokenSecret = '5bKjmZfyeoK4bhcO7GtOtFF5xMJdFVb0JIT4wkJmQPiiiSlZ8Z';

echo "=== Testing flatforsalebopal.tumblr.com ===\n";

// Test 1: User info
$url = "https://api.tumblr.com/v2/user/info";
$authHeader = getTumblrOAuthHeader($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, 'GET', []);

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

echo "1. /v2/user/info status: $code\n";
echo "Response: $resp\n\n";

// Test 2: Swap consumer key/secret and oauth token/secret in case they were inverted
$authHeader2 = getTumblrOAuthHeader($oauthToken, $oauthTokenSecret, $consumerKey, $consumerSecret, $url, 'GET', []);
$ch2 = curl_init($url);
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [$authHeader2],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp2 = curl_exec($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "2. Swapped keys /v2/user/info status: $code2\n";
echo "Response: $resp2\n";
