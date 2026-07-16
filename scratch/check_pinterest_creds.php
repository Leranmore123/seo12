<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT project_id, username, password, status FROM social_accounts WHERE platform='pinterest' AND username LIKE ?");
    $stmt->execute(['%lmth24820%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "Project ID: {$r['project_id']}, Username: {$r['username']}, Password length: " . strlen($r['password']) . ", Status: {$r['status']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
