<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: text/plain');

$apiKey = GEMINI_API_KEY;
echo "Gemini Key: " . substr($apiKey, 0, 15) . "... (" . strlen($apiKey) . " chars)\n";

$ch = curl_init("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey);
$payload = json_encode([
    'contents' => [['parts' => [['text' => 'Hello']]]]
]);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "cURL Error: " . $curlErr . "\n";
echo "Response:\n" . $res . "\n";
