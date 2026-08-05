<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM social_accounts WHERE id = 4987");
$acc = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== Account #4987 DB Details ===\n";
print_r($acc);

echo "\n=== Direct postToTumblr Call Test ===\n";
$res = postToTumblr($acc, "villa for sale Vadodara", "https://propertysdeal.in/propertys-details/villa-for-sale-vadodara", GEMINI_API_KEY, OPENAI_API_KEY, 1, [], 252);
print_r($res);
