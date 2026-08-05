<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$stmt = $db->query("SELECT id, username, email FROM users ORDER BY id ASC LIMIT 50");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== All Users in DB (" . count($users) . ") ===\n";
foreach ($users as $u) {
    echo "ID: {$u['id']} | Username: '{$u['username']}' | Email: '{$u['email']}'\n";
}
