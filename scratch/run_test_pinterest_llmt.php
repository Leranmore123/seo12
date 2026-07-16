<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$projectId = 211; // Property Deals (Real Estate)
$platform = 'pinterest';
$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$db = getDB();

// Fetch credentials for llmt2856@gmail.com
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND username='llmt2856@gmail.com' LIMIT 1");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "Error: Credentials not found in database for project 211 and Pinterest username llmt2856@gmail.com!\n";
    exit(1);
}

echo "=== STARTING PINTEREST AUTO-POST TEST FOR llmt2856@gmail.com VIA CMD ===\n";
echo "Username: {$creds['username']}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = seleniumPinterest($creds, $keyword, $targetSite, $projectId);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
