<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

$url = "https://learnmoretech.in/aws-training-in-kalyan-nagar";
$apiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';

$apiUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($url) . "&category=performance&strategy=mobile";
if (!empty($apiKey)) {
    $apiUrlWithKey = $apiUrl . "&key=" . urlencode($apiKey);
    $apiUrlHidden = $apiUrl . "&key=[HIDDEN]";
} else {
    $apiUrlWithKey = $apiUrl;
    $apiUrlHidden = $apiUrl;
}

echo "=== TESTING PAGESPEED API ===\n";
echo "Target Site: $url\n";
echo "API Key Configured: " . (empty($apiKey) ? "NO" : "YES") . "\n";
echo "Request URL: $apiUrlHidden\n\n";

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

echo "HTTP Status Code: $httpCode\n";
if (!empty($curlErr)) {
    echo "cURL Error: $curlErr\n";
}

if ($httpCode === 200 && $response) {
    echo "Response received successfully.\n";
    $data = json_decode($response, true);
    $score = $data['lighthouseResult']['categories']['performance']['score'] ?? null;
    echo "Decoded Score: " . var_export($score, true) . "\n";
} else {
    echo "Response Body Snippet (First 500 chars):\n";
    echo substr($response, 0, 500) . "\n";
}
