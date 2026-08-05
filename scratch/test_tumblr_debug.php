<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM social_accounts WHERE platform = 'tumblr' AND project_id = 240");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== DB Row for Project #240 (tumblr) ===\n";
print_r($row);
