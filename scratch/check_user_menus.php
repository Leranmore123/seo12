<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

$db = getDB();
$stmt = $db->prepare("SELECT id, username, role, allowed_menus FROM users WHERE username = 'Rahul_LMT'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== USER PERMISSION CHECK ===\n";
if ($user) {
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Allowed Menus raw: ";
    var_dump($user['allowed_menus']);
    
    if ($user['allowed_menus']) {
        $allowed = array_map('trim', explode(',', strtolower($user['allowed_menus'])));
        echo "Allowed Menus array: " . json_encode($allowed) . "\n";
        echo "Contains 'create-login-account'? " . (in_array('create-login-account', $allowed) ? "YES" : "NO") . "\n";
    }
} else {
    echo "User Rahul_LMT not found.\n";
}
