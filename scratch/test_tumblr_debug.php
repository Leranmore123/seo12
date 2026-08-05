<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

echo "=== Projects Table Columns & Sample Data ===\n";
$stmt = $db->query("SELECT * FROM projects LIMIT 10");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($projects)) {
    echo "Columns: " . implode(', ', array_keys($projects[0])) . "\n\n";
    foreach ($projects as $p) {
        print_r($p);
    }
} else {
    echo "Projects table is empty!\n";
}
