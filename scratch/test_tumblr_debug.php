<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$stmt = $db->query("SELECT id, project_id, username, api_key, password, status, created_at FROM social_accounts WHERE platform = 'tumblr' ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== All Social Accounts for Tumblr in DB (" . count($rows) . ") ===\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Project: {$r['project_id']} | Blog: '{$r['username']}' | Key: " . substr($r['api_key'], 0, 10) . "... | Created: {$r['created_at']}\n";
}
