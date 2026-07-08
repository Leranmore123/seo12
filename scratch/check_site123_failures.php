<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, project_id, status, error_message, created_at FROM backlink_queue WHERE platform = 'site123' ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'site123_queue' => $queue
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
