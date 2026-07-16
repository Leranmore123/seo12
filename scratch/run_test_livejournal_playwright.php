<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='livejournal' AND status='active' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "No active LiveJournal account found in the database. Trying any LiveJournal account...\n";
    $stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='livejournal' LIMIT 1");
    $stmt->execute();
    $creds = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$creds) {
    echo "Error: No LiveJournal account found in the database!\n";
    exit(1);
}

$username = $creds['username'];
$password = decodePass($creds['password']);

$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';
$aiTitle = "Guide on " . $keyword;
$aiBody = "<p>Looking for the best " . $keyword . "? Learnmore Technologies offers expert-led training with placement support. Register today! Visit <a href='" . $targetSite . "'>" . $targetSite . "</a> for more details.</p>";

$tmpFile = sys_get_temp_dir() . '/lj_test_content_' . time() . '.txt';
file_put_contents($tmpFile, $aiBody);

$args = [$username, $password, $keyword, $targetSite, $aiTitle, '', $tmpFile];

echo "=== STARTING LIVEJOURNAL PLAYWRIGHT AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$username}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = runSeleniumScript('livejournal_post_playwright.py', $args, 240);

@unlink($tmpFile);

echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
