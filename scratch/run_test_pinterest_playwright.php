<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$projectId = 211; // Property Deals (Real Estate)
$platform = 'pinterest';
$keyword = 'Property in Gota Ahmedabad';
$targetSite = 'https://propertysdeal.in/buy/residential-property-in-ahmedabad/in-gota/';

$db = getDB();

// Fetch credentials for lmth24820@gmail.com
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND username='lmth24820@gmail.com' LIMIT 1");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    echo "Error: Credentials not found in database for project 211 and Pinterest username lmth24820@gmail.com!\n";
    exit(1);
}

$email    = $creds['username'];
$password = decodePass($creds['password']);

$imagePath = '';
$stmt = $db->prepare("SELECT post_image FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$img = $stmt->fetchColumn();
if ($img) {
    $imagePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $img;
}

$aiTitle = "Discover Your Dream " . $keyword . " Today!";
$aiDesc  = "Looking for the best " . $keyword . "? Learnmore Technologies offers expert-led training with placement support. Register today!";

$args = [$email, $password, $keyword, $targetSite];
if ($imagePath && file_exists($imagePath)) {
    $args[] = $imagePath;
} else {
    $args[] = '';
}
$args[] = $aiTitle;
$args[] = $aiDesc;

echo "=== STARTING PINTEREST PLAYWRIGHT AUTO-POST TEST VIA CMD ===\n";
echo "Username: {$email}\n";
echo "Keyword: {$keyword}\n";
echo "Target URL: {$targetSite}\n\n";

$result = runSeleniumScript('pinterest_post_playwright.py', $args, 240);
echo "\n=== EXECUTION RESULT ===\n";
print_r($result);
