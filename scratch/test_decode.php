<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

$url = "https://learnmoretech.in/aws-training-in-kalyan-nagar";
$apiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';
$apiUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($url) . "&category=performance&strategy=mobile";
if (!empty($apiKey)) {
    $apiUrl .= "&key=" . urlencode($apiKey);
}

echo "=== DECODE DIAGNOSTIC ===\n";
echo "Fetching PageSpeed data...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 50,
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

if ($httpCode === 200 && $response) {
    echo "Response size: " . strlen($response) . " bytes\n";
    $data = json_decode($response, true);
    if ($data === null) {
        echo "JSON Decode Error: " . json_last_error_msg() . "\n";
    } else {
        echo "JSON Decoded successfully!\n";
        $score = $data['lighthouseResult']['categories']['performance']['score'] ?? 'NOT FOUND';
        echo "Lighthouse Performance Score raw value: ";
        var_dump($score);
        if ($score !== 'NOT FOUND' && $score !== null) {
            echo "Calculated Score: " . round($score * 100) . "\n";
        }
    }
} else {
    echo "Failed to fetch response.\n";
}
