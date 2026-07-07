<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../selenium/selenium-bridge.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=211");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$row) {
    if ($row['platform'] === 'tumblr') {
        $decrypted = base64_decode($row['password'] ?? '');
        $parts = explode(':', $decrypted);
        $row['tumblr_token'] = $parts[0] ?? '';
        $row['tumblr_token_secret'] = $parts[1] ?? '';
    } else {
        $row['decrypted_password'] = decodePass($row['password'] ?? '');
    }
}

print_r($rows);
