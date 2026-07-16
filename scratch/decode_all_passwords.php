<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

echo "=== DECODED LIVEJOURNAL ACCOUNTS ===\n";
$stmt = $db->prepare("SELECT id, project_id, username, password FROM social_accounts WHERE platform='livejournal'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $decoded = decodePass($row['password']);
    echo "ID: {$row['id']} | Project: {$row['project_id']} | User: {$row['username']} | Plaintext: {$decoded}\n";
}
