<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';
$db = getDB();

echo "=== RAW INDEX DUMP ===\n";
try {
    $indexes = $db->query("SHOW INDEX FROM backlinks")->fetchAll(PDO::FETCH_ASSOC);
    print_r($indexes);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
