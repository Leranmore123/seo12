<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$consumerKey = 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS';
$consumerSecret = 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ';
$oauthToken = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$oauthTokenSecret = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "=== Testing UTF-8 and rawurlencode Uppercase Fix ===\n\n";

$url = "https://api.tumblr.com/v2/blog/propertiesdelersblog.tumblr.com/post";
$postFields = [
    'type'  => 'text',
    'title' => 'Flat for Sale in Ahmedabad - Real Estate Guide',
    'body'  => "Looking for the best flat for sale in Ahmedabad? Properysdeal offers top residential options with luxury amenities.\n\nVisit https://propertysdeal.in/ for details.",
    'tags'  => 'properties dealers blog Ahmedabad,real estate,flats',
];

$authHeader = getTumblrOAuthHeader($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, 'POST', $postFields);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postFields, '', '&', PHP_QUERY_RFC3986),
    CURLOPT_HTTPHEADER     => [$authHeader, 'Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$code}\n";
echo "Response: {$resp}\n";
