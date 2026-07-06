<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=seo_system;charset=utf8mb4', 'root', '');
    $stmt = $pdo->query('SELECT * FROM users');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
