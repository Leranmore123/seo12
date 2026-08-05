<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$ckey = 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS';
$csec = 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ';
$otok = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$osec = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "=== Querying /v2/user/info to discover available blogs ===\n";

$url = "https://api.tumblr.com/v2/user/info";
$authHeader = getTumblrOAuthHeader($ckey, $csec, $otok, $osec, $url, 'GET', []);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [$authHeader],
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$code}\n";
echo "Response: {$resp}\n";
