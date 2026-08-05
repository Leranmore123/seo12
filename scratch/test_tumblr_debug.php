<?php
require_once __DIR__ . '/../config.php';

$blogs = [
    'flatforsalebopal.tumblr.com',
    'propertiesdelersblog.tumblr.com',
    'prahladnagarhomes.tumblr.com'
];

echo "=== Direct HTTP GET Test to Tumblr Web pages ===\n";
foreach ($blogs as $b) {
    $url = "https://{$b}/";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "URL: {$url} => HTTP {$code} (Length: " . strlen($html) . ")\n";
}
