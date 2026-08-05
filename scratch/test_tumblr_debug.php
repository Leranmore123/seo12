<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$blog = 'propertiesdelersblog.tumblr.com';
$consumerKey = 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS';
$consumerSecret = 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ';
$oauthToken = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$oauthTokenSecret = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "=== Real Post Test for propertiesdelersblog.tumblr.com ===\n";

$creds = [
    'username' => $blog,
    'api_key' => $consumerKey,
    'api_secret' => $consumerSecret,
    'password' => $oauthToken . ':' . $oauthTokenSecret
];

$result = postToTumblr($creds, "flat for sale in Ahmedabad", "https://propertysdeal.in/propertys-details/flat-for-sale-ahmedabad", GEMINI_API_KEY, OPENAI_API_KEY, 1, [], 244);

print_r($result);
