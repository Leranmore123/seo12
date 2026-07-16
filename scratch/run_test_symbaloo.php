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
    // Fallback to first available Symbaloo credential
    $stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? LIMIT 1");
    $stmt->execute([$projectId, $platform]);
    $creds = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$creds) {
    echo "Error: Symbaloo credentials not found in database for project 211!\n";
    exit(1);
}

echo "=== STARTING SYMBALOO AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$creds['username']}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = seleniumSymbaloo($creds, $keyword, $targetSite);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
