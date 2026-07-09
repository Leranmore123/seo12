<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

$url = "https://learnmoretech.in/aws-training-in-kalyan-nagar";
$apiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';

$apiUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($url) . "&category=performance&strategy=mobile";
if (!empty($apiKey)) {
    $apiUrlWithKey = $apiUrl . "&key=" . urlencode($apiKey);
} else {
    $apiUrlWithKey = $apiUrl;
}

echo "=== DIAGNOSING PAGESPEED WITH KEY ===\n";
echo "Key loaded: " . ($apiKey ? substr($apiKey, 0, 8) . "..." : "NONE") . "\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrlWithKey,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 45,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($curlErr) {
    echo "cURL Error: $curlErr\n";
}

echo "Response Body:\n";
echo $response . "\n";
