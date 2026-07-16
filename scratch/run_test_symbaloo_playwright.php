<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$projectId = 211; // Property Deals (Real Estate)
$platform = 'symbaloo';
$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$db = getDB();

// Fetch credentials for lmth24820@gmail.com
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND username='lmth24820@gmail.com' LIMIT 1");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "Error: Symbaloo credentials not found in database for project 211!\n";
    exit(1);
}

$email    = $creds['username'];
$password = decodePass($creds['password']);
$customMix = $creds['api_key'] ?? '';
$aiDesc = '';

$args = [$email, $password, $keyword, $targetSite, $customMix, $aiDesc];

echo "=== STARTING SYMBALOO PLAYWRIGHT AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$email}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = runSeleniumScript('symbaloo_post_playwright.py', $args, 240);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
