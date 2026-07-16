<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='minds' AND status='active' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "No active Minds account found in the database. Trying any Minds account...\n";
    $stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='minds' LIMIT 1");
    $stmt->execute();
    $creds = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$creds) {
    echo "Error: No Minds account found in the database at all!\n";
    exit(1);
}

$projectId = $creds['project_id'];
$email = $creds['username'];
$password = decodePass($creds['password']);

$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$aiTitle = "Guide on " . $keyword;
$aiContent = "Looking for the best " . $keyword . "? Visit " . $targetSite . " for details.";

$args = [$email, $password, $keyword, $targetSite, $aiTitle, $aiContent];

echo "=== STARTING MINDS PLAYWRIGHT AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$email}\n";
echo "Project ID: {$projectId}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = runSeleniumScript('minds_post_playwright.py', $args, 240);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
