<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/selenium/selenium-bridge.php';

$projectId = 208; // Project ID 208
$platform  = 'symbaloo';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=?");
$stmt->execute([$projectId, $platform]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($accounts)) {
    die("Error: No Symbaloo accounts found for Project ID {$projectId}\n");
}

echo "Found " . count($accounts) . " Symbaloo accounts in DB.\n\n";

// Target site and keyword
$keyword = "Best Web Design Agency";
$site    = "https://skyranksolution-bice.vercel.app/services";

foreach ($accounts as $idx => $creds) {
    echo "----------------------------------------\n";
    echo "Testing Account #" . ($idx + 1) . ": " . $creds['username'] . "\n";
    echo "Custom Webmix URL: " . ($creds['api_key'] ?: 'None (Default)') . "\n";
    echo "Running seleniumSymbaloo...\n";
    
    $res = seleniumSymbaloo($creds, $keyword, $site);
    print_r($res);
    echo "\n";
}
