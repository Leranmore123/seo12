<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE backlink_queue SET status = 'pending', error_message = NULL WHERE id = 46");
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Task 46 reset to pending status'
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
