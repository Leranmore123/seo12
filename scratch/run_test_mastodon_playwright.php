<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='mastodon' AND status='active' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "No active Mastodon account found in the database. Trying any Mastodon account...\n";
    $stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='mastodon' LIMIT 1");
    $stmt->execute();
    $creds = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$creds) {
    echo "Error: No Mastodon account found in the database!\n";
    exit(1);
}

$email = $creds['username'];
$password = decodePass($creds['password']);

$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$args = [$email, $password, $keyword, $targetSite];

echo "=== STARTING MASTODON PLAYWRIGHT AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$email}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = runSeleniumScript('mastodon_setup_playwright.py', $args, 240);

echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
