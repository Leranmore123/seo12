<?php
require_once __DIR__ . '/../config.php';

session_start();

$consumerKey = '0bZqdLtRzavMR5m625q6kYofTl8RBsf6qhjdVonpvygScbCtyj';
$consumerSecret = 'Oa6B06dJLamcdyG3utNnK9SatAz0Nsz0kwauYM3cwR2ZdaePhK';

// Callback URL back to this script
$host = $_SERVER['HTTP_HOST'];
$callbackUrl = "http://" . $host . "/scratch/tumblr_oauth_helper.php";

if (!isset($_GET['oauth_token'])) {
    // Step 1: Get Request Token
    $url = 'https://www.tumblr.com/oauth/request_token';
    $method = 'POST';
    
    $nonce = md5(uniqid(rand(), true));
    $timestamp = time();
    
    $oauthParams = [
        'oauth_callback' => $callbackUrl,
        'oauth_consumer_key' => $consumerKey,
        'oauth_nonce' => $nonce,
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => $timestamp,
        'oauth_version' => '1.0'
    ];
    
    ksort($oauthParams);
    
    $queryParts = [];
    foreach ($oauthParams as $key => $val) {
        $queryParts[] = rawurlencode($key) . '=' . rawurlencode($val);
    }
    $queryString = implode('&', $queryParts);
    
    $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode($queryString);
    $signatureKey = rawurlencode($consumerSecret) . '&';
    $signature = base64_encode(hash_hmac('sha1', $baseString, $signatureKey, true));
    
    $oauthParams['oauth_signature'] = $signature;
    
    $headerParts = [];
    foreach ($oauthParams as $key => $val) {
        $headerParts[] = $key . '="' . rawurlencode($val) . '"';
    }
    $authHeader = 'Authorization: OAuth ' . implode(', ', $headerParts);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$authHeader],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "<h3>Tumblr OAuth Initial Step</h3>";
        echo "<p style='color:red;'>Error getting request token (HTTP $httpCode): " . htmlspecialchars($response) . "</p>";
        echo "<p>To resolve this, please edit your Tumblr application settings at <a href='https://www.tumblr.com/oauth/apps' target='_blank'>tumblr.com/oauth/apps</a> and set the <b>Default Callback URL</b> to exactly:</p>";
        echo "<code>" . htmlspecialchars($callbackUrl) . "</code>";
        exit;
    }
    
    parse_str($response, $tokenData);
    if (!empty($tokenData['oauth_token']) && !empty($tokenData['oauth_token_secret'])) {
        $_SESSION['oauth_token_secret'] = $tokenData['oauth_token_secret'];
        $authUrl = "https://www.tumblr.com/oauth/authorize?oauth_token=" . $tokenData['oauth_token'];
        echo "<h3>Tumblr OAuth Flow</h3>";
        echo "<p>To get your Access Tokens, please click the button below to authorize the application:</p>";
        echo "<a href='$authUrl' style='display:inline-block;background:#00b4d8;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;font-weight:bold;'>Authorize Application</a>";
    } else {
        echo "Failed to parse request token: " . htmlspecialchars($response);
    }
} else {
    // Step 2: Callback - exchange Request Token for Access Token
    $oauthToken = $_GET['oauth_token'];
    $oauthVerifier = $_GET['oauth_verifier'];
    $oauthTokenSecret = $_SESSION['oauth_token_secret'] ?? '';
    
    $url = 'https://www.tumblr.com/oauth/access_token';
    $method = 'POST';
    
    $nonce = md5(uniqid(rand(), true));
    $timestamp = time();
    
    $oauthParams = [
        'oauth_consumer_key' => $consumerKey,
        'oauth_nonce' => $nonce,
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => $timestamp,
        'oauth_token' => $oauthToken,
        'oauth_verifier' => $oauthVerifier,
        'oauth_version' => '1.0'
    ];
    
    ksort($oauthParams);
    
    $queryParts = [];
    foreach ($oauthParams as $key => $val) {
        $queryParts[] = rawurlencode($key) . '=' . rawurlencode($val);
    }
    $queryString = implode('&', $queryParts);
    
    $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode($queryString);
    $signatureKey = rawurlencode($consumerSecret) . '&' . rawurlencode($oauthTokenSecret);
    $signature = base64_encode(hash_hmac('sha1', $baseString, $signatureKey, true));
    
    $oauthParams['oauth_signature'] = $signature;
    
    $headerParts = [];
    foreach ($oauthParams as $key => $val) {
        $headerParts[] = $key . '="' . rawurlencode($val) . '"';
    }
    $authHeader = 'Authorization: OAuth ' . implode(', ', $headerParts);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$authHeader],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "Error getting access token (HTTP $httpCode): " . htmlspecialchars($response);
        exit;
    }
    
    parse_str($response, $accessTokenData);
    if (!empty($accessTokenData['oauth_token']) && !empty($accessTokenData['oauth_token_secret'])) {
        echo "<h2>🎉 Authorization Successful!</h2>";
        echo "<p>Here are your Tumblr Access Tokens. Copy these keys and paste them into your Tumblr credentials form:</p>";
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;max-width:600px;'>";
        echo "<tr><th>Key Name</th><th>Value to copy</th></tr>";
        echo "<tr><td><b>OAuth Token (Access Token)</b></td><td><code style='font-size:16px;background:#e9f5ff;padding:4px;display:block;word-break:break-all;'>" . htmlspecialchars($accessTokenData['oauth_token']) . "</code></td></tr>";
        echo "<tr><td><b>OAuth Token Secret (Access Token Secret)</b></td><td><code style='font-size:16px;background:#e9f5ff;padding:4px;display:block;word-break:break-all;'>" . htmlspecialchars($accessTokenData['oauth_token_secret']) . "</code></td></tr>";
        echo "</table>";
        echo "<br><a href='../submission-manager.php' style='color:#007bff;'>Go back to Project Manager</a>";
    } else {
        echo "Failed to parse access token: " . htmlspecialchars($response);
    }
}
