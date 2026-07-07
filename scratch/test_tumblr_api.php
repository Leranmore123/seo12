<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();

// Fetch the saved tumblr account credentials
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='tumblr' AND status='active' AND api_key != '' AND api_key IS NOT NULL ORDER BY id DESC LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch();

if (!$creds) {
    echo "No active Tumblr credentials saved.\n";
    exit;
}

$consumerKey = $creds['api_key'] ?? '';
$consumerSecret = $creds['api_secret'] ?? '';
$blogName = $creds['username'] ?? '';
$blogName = str_replace(['https://', 'http://'], '', $blogName);

$decrypted = base64_decode($creds['password'] ?? '');
$parts = explode(':', $decrypted);
$oauthToken = $parts[0] ?? '';
$oauthTokenSecret = $parts[1] ?? '';

echo "Blog Hostname: $blogName\n";
echo "Consumer Key:  " . substr($consumerKey, 0, 5) . "...\n";
echo "Consumer Sec:  " . substr($consumerSecret, 0, 5) . "...\n";
echo "OAuth Token:   " . substr($oauthToken, 0, 5) . "...\n";
echo "OAuth Secret:  " . substr($oauthTokenSecret, 0, 5) . "...\n\n";

$url = "https://api.tumblr.com/v2/blog/{$blogName}/post";

$postFields = [
    'type'  => 'text',
    'title' => 'Test Post ' . time(),
    'body'  => 'This is a test post to verify OAuth signature.',
    'tags'  => 'test,api',
];

// Helper to sign and execute request
function runTestRequest($consumerKey, $consumerSecret, $token, $tokenSecret, $url, $postFields, $signFields = true, $useRfc3986 = false) {
    $nonce = md5(uniqid(rand(), true));
    $timestamp = time();
    
    $oauthParams = [
        'oauth_consumer_key' => $consumerKey,
        'oauth_nonce' => $nonce,
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => $timestamp,
        'oauth_token' => $token,
        'oauth_version' => '1.0'
    ];
    
    // Sort parameters
    $sigParams = [];
    if ($signFields) {
        $sigParams = $postFields;
    }
    $allParams = array_merge($oauthParams, $sigParams);
    ksort($allParams);
    
    // Build query string
    $queryParts = [];
    foreach ($allParams as $key => $val) {
        $queryParts[] = rawurlencode($key) . '=' . rawurlencode($val);
    }
    $queryString = implode('&', $queryParts);
    
    // Base String
    $baseString = 'POST&' . rawurlencode($url) . '&' . rawurlencode($queryString);
    
    // Signature Key
    $signatureKey = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret);
    
    // Generate signature
    $signature = base64_encode(hash_hmac('sha1', $baseString, $signatureKey, true));
    
    // Add signature to OAuth parameters
    $oauthParams['oauth_signature'] = $signature;
    
    // Build Authorization Header
    $headerParts = [];
    foreach ($oauthParams as $key => $val) {
        $headerParts[] = $key . '="' . rawurlencode($val) . '"';
    }
    $authHeader = 'Authorization: OAuth ' . implode(', ', $headerParts);
    
    // Post fields encoding
    $postBody = $useRfc3986 
        ? http_build_query($postFields, '', '&', PHP_QUERY_RFC3986)
        : http_build_query($postFields);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postBody,
        CURLOPT_HTTPHEADER     => [$authHeader, 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'base_string' => $baseString
    ];
}

echo "=== TEST A: Sign with Fields (RFC1738 http_build_query) ===\n";
$resA = runTestRequest($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, $postFields, true, false);
echo "HTTP Code: " . $resA['code'] . "\n";
echo "Response:  " . $resA['response'] . "\n\n";

echo "=== TEST B: Sign with Fields (RFC3986 http_build_query) ===\n";
$resB = runTestRequest($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, $postFields, true, true);
echo "HTTP Code: " . $resB['code'] . "\n";
echo "Response:  " . $resB['response'] . "\n\n";

echo "=== TEST C: Sign WITHOUT Fields (OAuth params only) ===\n";
$resC = runTestRequest($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $url, $postFields, false, false);
echo "HTTP Code: " . $resC['code'] . "\n";
echo "Response:  " . $resC['response'] . "\n\n";
