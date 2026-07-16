<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

echo "=== MINDS ACCOUNTS ===\n";
$stmt = $db->prepare("SELECT id, project_id, username, password, status FROM social_accounts WHERE platform='minds'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Project: {$row['project_id']} | User: {$row['username']} | Pass Length: " . strlen($row['password']) . " | Status: {$row['status']}\n";
}

echo "\n=== LIVEJOURNAL ACCOUNTS ===\n";
$stmt = $db->prepare("SELECT id, project_id, username, password, status FROM social_accounts WHERE platform='livejournal'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Project: {$row['project_id']} | User: {$row['username']} | Pass Length: " . strlen($row['password']) . " | Status: {$row['status']}\n";
}

echo "\n=== MASTODON ACCOUNTS ===\n";
$stmt = $db->prepare("SELECT id, project_id, username, password, api_key, status FROM social_accounts WHERE platform='mastodon'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Project: {$row['project_id']} | User: {$row['username']} | Pass Length: " . strlen($row['password']) . " | Has Token: " . (empty($row['api_key']) ? 'No' : 'Yes') . " | Status: {$row['status']}\n";
}
