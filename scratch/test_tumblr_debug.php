<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$stmt = $db->query("SELECT u.id as user_id, u.username, u.email, p.id as project_id, p.name as project_name FROM users u LEFT JOIN projects p ON p.user_id = u.id WHERE u.username LIKE 'pd%' OR u.email LIKE 'pd%' ORDER BY u.id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== User to Project Mapping for pd1..pd40 ===\n";
echo "User ID | Username | Project ID | Project Name\n";
echo "-----------------------------------------------\n";
foreach ($rows as $r) {
    echo "{$r['user_id']} | {$r['username']} | " . ($r['project_id'] ?: 'NONE') . " | {$r['project_name']}\n";
}
