<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';
$db = getDB();

echo "=== DATABASE PROFILE ===\n";

try {
    $count = $db->query("SELECT COUNT(*) FROM backlinks")->fetchColumn();
    echo "Total backlinks count: $count\n\n";
    
    echo "=== INDEXES ON backlinks ===\n";
    $indexes = $db->query("SHOW INDEX FROM backlinks")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $idx) {
        echo "Table: {$idx['Table']} | Key_name: {$idx['Key_name']} | Column_name: {$idx['Column_name']} | Non_unique: {$idx['Non_unique']}\n";
    }
} catch (Throwable $e) {
    echo "Error checking indexes: " . $e->getMessage() . "\n";
}
