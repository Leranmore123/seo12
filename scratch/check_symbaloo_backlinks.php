<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT id, project_id, platform, backlink_url, created_at 
        FROM backlinks 
        WHERE platform='symbaloo' 
        ORDER BY id DESC LIMIT 5
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== LATEST SYMBALOO BACKLINKS ===\n";
    foreach ($rows as $r) {
        echo "ID: {$r['id']} | Project: {$r['project_id']} | Created: {$r['created_at']}\n";
        echo "  URL: {$r['backlink_url']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
