<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    
    echo "=== LATEST QUEUE ITEMS FOR lmth24820@gmail.com ===\n";
    $stmt = $db->query("
        SELECT q.id, q.project_id, q.status, q.error_message, q.updated_at, a.username 
        FROM backlink_queue q
        JOIN social_accounts a ON q.social_account_id = a.id
        WHERE q.platform = 'pinterest' AND a.username = 'lmth24820@gmail.com'
        ORDER BY q.id DESC LIMIT 5
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "Queue ID: {$r['id']} | Project: {$r['project_id']} | Status: {$r['status']} | Updated: {$r['updated_at']}\n";
        if ($r['error_message']) {
            echo "  Error: {$r['error_message']}\n";
        }
    }
    
    echo "\n=== LATEST SUCCESSFUL PINTEREST BACKLINKS ===\n";
    $stmt2 = $db->query("
        SELECT id, project_id, published_url, created_at 
        FROM backlinks 
        WHERE platform = 'pinterest' 
        ORDER BY id DESC LIMIT 5
    ");
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $r2) {
        echo "Backlink ID: {$r2['id']} | Project: {$r2['project_id']} | URL: {$r2['published_url']} | Created: {$r2['created_at']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
