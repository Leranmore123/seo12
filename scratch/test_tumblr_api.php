<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();

$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='tumblr' AND status='active' AND api_key != '' AND api_key IS NOT NULL ORDER BY id DESC LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch();

if (!$creds) {
    echo "No active Tumblr credentials saved.\n";
    exit;
}

$keyword = "AI SEO solutions";
$site = "https://skyranksolution-bice.vercel.app/services, https://skyranksolution-bice.vercel.app/, https://skyranksolution-bice.vercel.app/pricing, https://skyranksolution-bice.vercel.app/tools";

echo "Running real postToTumblr with project ID 211...\n";
$result = postToTumblr($creds, $keyword, $site, '', '', 1, [], 211);

echo "Result:\n";
print_r($result);
