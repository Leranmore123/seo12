<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();

// Fetch the saved tumblr account credentials
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE platform='tumblr' AND status='active' LIMIT 1");
$stmt->execute();
$creds = $stmt->fetch();

if (!$creds) {
    echo "No active Tumblr credentials saved. Go to social-accounts.php, fill the form, and save first.\n";
    exit;
}

echo "Found active Tumblr credentials for blog: " . $creds['username'] . "\n";
echo "Attempting to post to Tumblr via OAuth 1.0a API...\n";

$keyword = "SEO Agency";
$site = "https://skyranksolution-bice.vercel.app/";

// Make a mock project array
$project = [
    'target_keyword' => $keyword,
    'target_site' => $site,
    'website_url' => $site,
    'business_name' => 'SkyRank Solutions',
    'business_desc' => 'Top rated SEO and digital marketing agency.'
];

$result = postToTumblr($creds, $keyword, $site, '', '', 1, [], 0, $project['business_name'], $project['business_desc']);

echo "Result:\n";
print_r($result);
