<?php
require_once dirname(__DIR__) . '/config.php';

try {
    $db = getDB();
    $sql = "CREATE TABLE IF NOT EXISTS `backlink_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT NOT NULL,
        `social_account_id` INT NOT NULL,
        `platform` VARCHAR(50) NOT NULL,
        `keyword` VARCHAR(255) NOT NULL,
        `target_url` VARCHAR(255) NOT NULL,
        `status` ENUM('pending', 'processing', 'success', 'failed') DEFAULT 'pending',
        `published_url` VARCHAR(255) DEFAULT NULL,
        `error_message` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "SUCCESS: Table 'backlink_queue' created successfully.\n";
} catch (PDOException $e) {
    echo "ERROR: Could not create table: " . $e->getMessage() . "\n";
}
