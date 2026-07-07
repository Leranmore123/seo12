<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/selenium/selenium-bridge.php';

$projectId = 208; // Project ID 208
$platform  = 'symbaloo';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=?");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creds) {
    die("Error: No Symbaloo account found for Project ID {$projectId}\n");
}

echo "Running Symbaloo Selenium script via seleniumSymbaloo...\n";

// Target site and keyword
$keyword = "Best Web Design Agency";
$site    = "https://skyranksolution-bice.vercel.app/services";

$res = seleniumSymbaloo($creds, $keyword, $site);
print_r($res);
