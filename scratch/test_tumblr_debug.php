<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$stmt = $db->query("SELECT id, project_id, username, api_key, api_secret, password, status FROM social_accounts WHERE platform = 'tumblr'");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total Tumblr Social Accounts: " . count($accounts) . "\n";
foreach ($accounts as $acc) {
    echo "ID: {$acc['id']} | Project: {$acc['project_id']} | User: {$acc['username']} | Key: " . substr($acc['api_key'], 0, 10) . "... | Status: {$acc['status']}\n";
}
