<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, project_id, notes, status, created_at FROM backlinks WHERE platform='pinterest' AND status='pending' ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "=== PENDING PINTEREST BACKLINKS IN DATABASE ===\n";
    foreach ($rows as $r) {
        echo "ID: {$r['id']} | Project: {$r['project_id']} | Status: {$r['status']} | Created: {$r['created_at']}\n";
        echo "Notes: {$r['notes']}\n\n";
    }

    $stmt2 = $db->query("SELECT id, project_id, error_message, status, updated_at FROM backlink_queue WHERE platform='pinterest' AND status='failed' ORDER BY id DESC LIMIT 5");
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "=== FAILED PINTEREST QUEUE ITEMS IN DATABASE ===\n";
    foreach ($rows2 as $r) {
        echo "Queue ID: {$r['id']} | Project: {$r['project_id']} | Status: {$r['status']} | Updated: {$r['updated_at']}\n";
        echo "Error: {$r['error_message']}\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
