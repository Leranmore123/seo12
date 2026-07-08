<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Step 1: Alter table to add api_key column if missing
    try {
        $db->exec("ALTER TABLE users ADD COLUMN api_key VARCHAR(64) UNIQUE DEFAULT NULL");
        $altered = true;
    } catch (PDOException $e) {
        // Column probably already exists, which is fine
        $altered = false;
    }
    
    // Step 2: Populate api_key for users who do not have one
    $users = $db->query("SELECT id, username, api_key FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $updatedCount = 0;
    
    foreach ($users as $user) {
        if (empty($user['api_key'])) {
            // Generate a secure 32-byte API Key (hex encoded)
            $newKey = bin2hex(random_bytes(16));
            $stmt = $db->prepare("UPDATE users SET api_key = ? WHERE id = ?");
            $stmt->execute([$newKey, $user['id']]);
            $updatedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'altered_table' => $altered,
        'updated_users_count' => $updatedCount,
        'message' => 'API Key infrastructure migration completed successfully.'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
