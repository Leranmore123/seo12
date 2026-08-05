<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$strA = 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ';
$strB = 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS';
$strC = 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ';
$strD = 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C';

echo "=== Testing All Permutations for propertiesdelersblog.tumblr.com ===\n\n";

$keys = [$strA, $strB, $strC, $strD];
$count = 0;

foreach ($keys as $ckey) {
    foreach ($keys as $csec) {
        if ($csec === $ckey) continue;
        foreach ($keys as $otok) {
            if ($otok === $ckey || $otok === $csec) continue;
            foreach ($keys as $osec) {
                if ($osec === $ckey || $osec === $csec || $osec === $otok) continue;
                
                $count++;
                $url = "https://api.tumblr.com/v2/blog/propertiesdelersblog.tumblr.com/post";
                $postFields = [
                    'type'  => 'text',
                    'title' => 'Permutation Test ' . $count,
                    'body'  => 'Testing OAuth Keys',
                    'tags'  => 'test',
                ];
                
                $authHeader = getTumblrOAuthHeader($ckey, $csec, $otok, $osec, $url, 'POST', $postFields);
                
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query($postFields, '', '&', PHP_QUERY_RFC3986),
                    CURLOPT_HTTPHEADER     => [$authHeader, 'Content-Type: application/x-www-form-urlencoded'],
                    CURLOPT_TIMEOUT        => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($code === 201 || $code === 200) {
                    echo "🎉 SUCCESS HTTP {$code} on Permutation #{$count}!\n";
                    echo "Consumer Key: {$ckey}\n";
                    echo "Consumer Secret: {$csec}\n";
                    echo "OAuth Token: {$otok}\n";
                    echo "OAuth Token Secret: {$osec}\n";
                    echo "Response: {$resp}\n\n";
                    exit;
                } else {
                    echo "Attempt #{$count} => HTTP {$code}\n";
                }
            }
        }
    }
}

echo "All permutations tested.\n";
