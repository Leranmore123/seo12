<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();

    echo "========================================\n";
    echo "LATEST 5 QUEUE ITEMS:\n";
    echo "========================================\n";
    $stmt = $db->query("SELECT id, project_id, social_account_id, platform, keyword, target_url, status, error_message, updated_at FROM backlink_queue ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "Queue ID: {$r['id']}, Project ID: {$r['project_id']}, Platform: {$r['platform']}, Keyword: {$r['keyword']}, URL: {$r['target_url']}, Status: {$r['status']}, Error: {$r['error_message']}, Updated: {$r['updated_at']}\n";
    }

    echo "\n========================================\n";
    echo "LATEST 5 BACKLINKS CREATED:\n";
    echo "========================================\n";
    $stmt2 = $db->query("SELECT id, project_id, platform, keyword, target_url, backlink_url, post_title, created_at FROM backlinks ORDER BY id DESC LIMIT 5");
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $r) {
        echo "Backlink ID: {$r['id']}, Project ID: {$r['project_id']}, Platform: {$r['platform']}, Keyword: {$r['keyword']}, URL: {$r['target_url']}, Backlink: {$r['backlink_url']}, Title: {$r['post_title']}, Created: {$r['created_at']}\n";
    }

} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
