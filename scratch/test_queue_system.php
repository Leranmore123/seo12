<?php
require_once dirname(__DIR__) . '/config.php';

$db = getDB();
$projectId = 211;
$platform = 'livejournal';

// Fetch active social account
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND status='active' LIMIT 1");
$stmt->execute([$projectId, $platform]);
$creds = $stmt->fetch();

if (!$creds) {
    die("No active LiveJournal account found for Project {$projectId}.\n");
}

// Clear queue to ensure a clean test run
$db->exec("DELETE FROM backlink_queue");

// Queue a new task
$insertStmt = $db->prepare('INSERT INTO backlink_queue (project_id, social_account_id, platform, keyword, target_url, status) VALUES (?, ?, ?, ?, ?, "pending")');
$insertStmt->execute([
    $projectId,
    $creds['id'],
    $platform,
    "Best Web Design Agency",
    "https://skyranksolution-bice.vercel.app/services"
]);

echo "SUCCESS: Test task queued in backlink_queue table.\n";
