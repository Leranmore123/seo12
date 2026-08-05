<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$consumerKey = 'QRWO9l1KifJyiBJICcvOkHS5J0XM4UT3AzdjRdeWocev3mEb8z';
$consumerSecret = 'C0lFnqNOncJ5zbCw3fFLjQxpBW4EiefYfWBR6mWOe3Kc7n8XYa';
$oauthToken = '9vldaudhMnkJt9FkTpDF6MQ6i7d7s86imqAnpylT5zEQBuowP1';
$oauthTokenSecret = 'l13CukH4Obp1PBWXchWl6cj6qrMEp3l4UvxECdo5LaZ9NFok3r';
$blogName = 'howtoverifypropertyingujara';

echo "=== Testing Real Tumblr Post to {$blogName} ===\n\n";

$creds = [
    'api_key'    => $consumerKey,
    'api_secret' => $consumerSecret,
    'username'   => $blogName,
    'password'   => base64_encode($oauthToken . ':' . $oauthTokenSecret)
];

$res = postToTumblr($creds, 'property in Gujarat', 'https://propertysdeal.in/propertys-details/property-in-gujarat', OPENAI_API_KEY, OPENAI_API_KEY, 1, [], 214);

echo "Post Result:\n";
print_r($res);
