<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, platform, error_message, updated_at FROM backlink_queue WHERE status = 'failed' AND platform = 'pinterest' ORDER BY id DESC LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "No failed Pinterest tasks found in the queue.\n";
    } else {
        echo "========================================\n";
        echo "LAST 5 FAILED PINTEREST TASKS:\n";
        echo "========================================\n";
        foreach ($results as $row) {
            echo "Task ID: {$row['id']}\n";
            echo "Failed At: {$row['updated_at']}\n";
            echo "Error Detail: " . ($row['error_message'] ?? 'Unknown Error') . "\n";
            echo "----------------------------------------\n";
        }
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
