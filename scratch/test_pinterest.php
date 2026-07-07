<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();

// Fetch Pinterest credentials for project 211
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=211 AND platform='pinterest'");
$stmt->execute();
$all_creds = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$all_creds) {
    echo "No Pinterest credentials found for project 211.\n";
    exit;
}

$keyword = "AI Powered SEO Agency";
$site = "https://skyranksolution-bice.vercel.app/services";

foreach ($all_creds as $creds) {
    echo "\n----------------------------------------\n";
    echo "Running Pinterest Selenium script for account: {$creds['username']}\n";
    $res = seleniumPinterest($creds, $keyword, $site, 211);
    print_r($res);
}
