<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/selenium/selenium-bridge.php';
require_once dirname(__DIR__) . '/ai-content.php';

$projectId = 211; // Project ID
$platform  = 'livejournal';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=?");
$stmt->execute([$projectId, $platform]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($accounts)) {
    die("Error: No LiveJournal accounts found for Project ID {$projectId}\n");
}

echo "Found " . count($accounts) . " LiveJournal accounts in DB.\n\n";

// Target site and keyword
$keyword = "Best Web Design Agency";
$site    = "https://skyranksolution-bice.vercel.app/services";

foreach ($accounts as $idx => $creds) {
    echo "----------------------------------------\n";
    echo "Testing Account #" . ($idx + 1) . ": " . $creds['username'] . " (Project ID: " . $creds['project_id'] . ")\n";
    echo "Running seleniumLiveJournal...\n";
    
    $res = seleniumLiveJournal($creds, $keyword, $site, $projectId);
    print_r($res);
    echo "\n";
}
