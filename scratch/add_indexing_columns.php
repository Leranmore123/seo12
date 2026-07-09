<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    $addedStatus = false;
    $addedTime = false;
    
    // 1. Add indexing_status column
    try {
        $db->exec("ALTER TABLE backlinks ADD COLUMN indexing_status VARCHAR(50) DEFAULT 'unchecked'");
        $addedStatus = true;
    } catch (PDOException $e) {
        // Column probably already exists, ignore
    }
    
    // 2. Add last_index_checked_at column
    try {
        $db->exec("ALTER TABLE backlinks ADD COLUMN last_index_checked_at DATETIME DEFAULT NULL");
        $addedTime = true;
    } catch (PDOException $e) {
        // Column probably already exists, ignore
    }
    
    // Set existing backlinks to 'unchecked' if empty
    $db->exec("UPDATE backlinks SET indexing_status = 'unchecked' WHERE indexing_status IS NULL OR indexing_status = ''");
    
    echo json_encode([
        'success' => true,
        'added_indexing_status_column' => $addedStatus,
        'added_last_checked_column' => $addedTime,
        'message' => 'Backlinks table migrated successfully with indexing status columns.'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
