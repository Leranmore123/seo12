<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$consumerKey = 'QRWO9l1KifJyiBJICcvOkHS5J0XM4UT3AzdjRdeWocev3mEb8z';
$consumerSecret = 'C0lFnqNOncJ5zbCw3fFLjQxpBW4EiefYfWBR6mWOe3Kc7n8XYa';
$oauthToken = '9vldaudhMnkJt9FkTpDF6MQ6i7d7s86imqAnpylT5zEQBuowP1';
$oauthTokenSecret = 'l13CukH4Obp1PBWXchWl6cj6qrMEp3l4UvxECdo5LaZ9NFok3r';

echo "=== Testing Fresh Tumblr Authorized Account ===\n\n";

// Step 1: User Info Test
$url = "https://api.tumblr.com/v2/user/info";
$authHeader = getTumblrOAuthHeader($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, 'GET', []);

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

echo "User Info Response Code: HTTP {$code}\n";
$data = json_decode($resp, true);

if ($code === 200 && isset($data['response']['user']['blogs'])) {
    echo "🎉 SUCCESS! Connected User: " . $data['response']['user']['name'] . "\n";
    echo "Available Blogs under this account:\n";
    foreach ($data['response']['user']['blogs'] as $b) {
        echo " - " . $b['name'] . " (" . $b['url'] . ")\n";
    }
} else {
    echo "Error response: {$resp}\n";
}
