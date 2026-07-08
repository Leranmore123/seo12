<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Get last 5 queue items
    $stmt = $db->prepare("SELECT id, project_id, social_account_id, platform, keyword, status, published_url, error_message, created_at FROM backlink_queue ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get last 5 backlinks
    $stmt2 = $db->prepare("SELECT id, project_id, platform, backlink_url, post_title, created_at FROM backlinks ORDER BY id DESC LIMIT 5");
    $stmt2->execute();
    $backlinks = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Get count of active livejournal accounts
    $stmt3 = $db->prepare("SELECT id, username, status FROM social_accounts WHERE platform = 'livejournal'");
    $stmt3->execute();
    $accounts = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'queue' => $queue,
        'backlinks' => $backlinks,
        'accounts' => $accounts
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
