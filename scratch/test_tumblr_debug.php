<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

echo "=== Cleaning Up Legacy Failed Tumblr Queue Test Tasks ===\n\n";

// Update legacy failed tumblr queue items to pending or delete them
$db->exec("DELETE FROM backlink_queue WHERE platform = 'tumblr' AND status = 'failed'");
echo "Cleared old failed Tumblr queue test items.\n";

// Queue a new fresh test task for Project #214
$acc = $db->query("SELECT id, project_id FROM social_accounts WHERE platform = 'tumblr' AND status = 'active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($acc) {
    $stmt = $db->prepare("INSERT INTO backlink_queue (project_id, social_account_id, platform, keyword, target_url, status) VALUES (?, ?, 'tumblr', 'real estate in Gujarat', 'https://propertysdeal.in/propertys-details/property-in-gujarat', 'pending')");
    $stmt->execute([$acc['project_id'], $acc['id']]);
    $newId = $db->lastInsertId();
    echo "🎉 Successfully Queued Fresh Task ID: {$newId} for Project #{$acc['project_id']}\n";
}
