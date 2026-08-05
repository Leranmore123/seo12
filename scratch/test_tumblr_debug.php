<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$blog = 'flatforsalebopal.tumblr.com';

$keysToTest = [
    'jZJvMh6KxsS8iw7tMgbrCKXDBW9IOPm9GUx26vWvBajd5ikxUq',
    'D9TDCp4AKHvF7Xr1TFXWHzp3EmDch2ns0FmtLtiOnPca296RiE',
    'Zxg1DOtbVMJgUpPDBnKwItPLfkI2112dfnmx4qvZNf6FmAbbei',
    '5bKjmZfyeoK4bhcO7GtOtFF5xMJdFVb0JIT4wkJmQPiiiSlZ8Z'
];

echo "=== Testing API Key Validity on Public Endpoint ===\n";
foreach ($keysToTest as $idx => $key) {
    $url = "https://api.tumblr.com/v2/blog/{$blog}/info?api_key=" . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Key #$idx (" . substr($key, 0, 10) . "...): HTTP Code $code\n";
    if ($code === 200) {
        echo "   ✅ THIS IS THE VALID CONSUMER KEY / API KEY!\n";
    } else {
        echo "   Response: " . substr($resp, 0, 120) . "\n";
    }
}
