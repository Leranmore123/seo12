<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB();
    $platform = isset($argv[1]) ? strtolower(trim($argv[1])) : '';

    if ($platform !== '') {
        $stmt = $db->prepare("SELECT id, platform, error_message, updated_at FROM backlink_queue WHERE status = 'failed' AND platform = ? ORDER BY id DESC LIMIT 5");
        $stmt->execute([$platform]);
        $title = "LAST 5 FAILED " . strtoupper($platform) . " TASKS";
    } else {
        $stmt = $db->prepare("SELECT id, platform, error_message, updated_at FROM backlink_queue WHERE status = 'failed' ORDER BY id DESC LIMIT 10");
        $stmt->execute();
        $title = "LAST 10 FAILED TASKS (ALL PLATFORMS)";
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "========================================\n";
    echo "$title\n";
    echo "========================================\n";

    if (empty($results)) {
        echo "No failed tasks found.\n";
    } else {
        foreach ($results as $row) {
            echo "Task ID: {$row['id']}\n";
            echo "Platform: " . ucfirst($row['platform']) . "\n";
            echo "Failed At: {$row['updated_at']}\n";
            echo "Error Detail: " . ($row['error_message'] ?? 'Unknown Error') . "\n";
            echo "----------------------------------------\n";
        }
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
