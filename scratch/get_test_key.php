<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    $user = $db->query("SELECT username, api_key FROM users WHERE api_key IS NOT NULL LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo json_encode($user, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
