<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    $uploadDir = dirname(__DIR__) . '/uploads/';

    foreach ([211, 203, 208] as $id) {
        $stmt = $db->prepare("SELECT id, business_name, post_image FROM projects WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "Project ID: {$row['id']} | Business: {$row['business_name']}\n";
            echo "  post_image in DB: " . ($row['post_image'] ?: 'EMPTY') . "\n";
            if ($row['post_image']) {
                $fullPath = $uploadDir . $row['post_image'];
                echo "  Expected path: {$fullPath}\n";
                echo "  File exists on disk: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
                if (file_exists($fullPath)) {
                    echo "  File size: " . filesize($fullPath) . " bytes\n";
                }
            }
        } else {
            echo "Project {$id} not found!\n";
        }
        echo "----------------------------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
