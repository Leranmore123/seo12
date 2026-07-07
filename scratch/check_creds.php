<?php
require_once __DIR__ . '/../config.php';
$db = getDB();
$stmt = $db->prepare("SELECT id, project_id, platform, username, api_key, api_secret, status FROM social_accounts WHERE project_id=211");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
