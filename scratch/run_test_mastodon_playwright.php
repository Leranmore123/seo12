<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();

// Update Project 211 Mastodon credentials to the correct ones
$db->exec("UPDATE social_accounts SET username='rahul.learnmore12@gmail.com', password='" . base64_encode('@disha12@') . "' WHERE id=4506");
echo "Updated database Mastodon account (ID: 4506) to rahul.learnmore12@gmail.com / @disha12@\n\n";

$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='mastodon' AND status='active' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

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
