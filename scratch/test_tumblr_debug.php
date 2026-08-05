<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

echo "=== Testing propertiesdelersblog.tumblr.com posting ===\n";

$acc = [
    'username'   => 'propertiesdelersblog.tumblr.com',
    'api_key'    => 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS',
    'api_secret' => 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ',
    'password'   => 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ:rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C'
];

$res = postToTumblr($acc, "properties dealers blog Ahmedabad", "https://propertysdeal.in/propertys-details/flat-for-sale-ahmedabad", GEMINI_API_KEY, OPENAI_API_KEY, 1, [], 243);

print_r($res);
