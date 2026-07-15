<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$projectId = 211;
$platform = 'minds';
$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$db = getDB();

// Fetch social account credentials
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND status='active' LIMIT 1");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "Error: No active social credentials found for project {$projectId} and platform {$platform}\n";
    exit(1);
}

echo "=== STARTING MINDS AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$creds['username']}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = seleniumMicroBlog($platform, $creds, $keyword, $targetSite, '', '', $projectId);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
