<?php
require_once dirname(__DIR__) . '/config.php';
$db = getDB();

header('Content-Type: text/plain');

$stmt = $db->prepare("SELECT user_id, website_url FROM projects WHERE id = 211");
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if ($project) {
    echo "Project 211 Owner User ID: " . $project['user_id'] . "\n";
    echo "Website URL: " . $project['website_url'] . "\n";
    
    // Check user info
    $uStmt = $db->prepare("SELECT username, role FROM users WHERE id = ?");
    $uStmt->execute([$project['user_id']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "Username: " . $user['username'] . "\n";
        echo "Role: " . $user['role'] . "\n";
    }
} else {
    echo "Project 211 not found in database.\n";
}
