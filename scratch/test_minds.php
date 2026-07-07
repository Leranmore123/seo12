<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();

// Fetch Minds credentials for project 211
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=211 AND platform='minds' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch();

if (!$creds) {
    echo "No Minds credentials found for project 211.\n";
    exit;
}

$keyword = "AI Powered SEO Agency";
$site = "https://skyranksolution-bice.vercel.app/services";

echo "Running Minds Selenium script via seleniumMicroBlog...\n";
$res = seleniumMicroBlog('minds', $creds, $keyword, $site, 'Test Title', 'Test Content', 211);
print_r($res);
