<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config.php';

// Simulate session for user 24 (Rahul_LMT)
$_SESSION['user_id'] = 24;
$_SESSION['username'] = 'Rahul_LMT';
$_SESSION['role'] = 'client';

// Capture output of navbar.php
ob_start();
include dirname(__DIR__) . '/includes/navbar.php';
$html = ob_get_clean();

echo "=== NAVBAR RENDER TEST ===\n";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'none') . "\n";
echo "Session role: " . ($_SESSION['role'] ?? 'none') . "\n";
echo "Allowed menus in DB: ";
$db = getDB();
$stmt = $db->prepare("SELECT allowed_menus FROM users WHERE id = 24");
$stmt->execute();
var_dump($stmt->fetchColumn());

echo "Allowed Menus after include: ";
var_dump($allowedMenus);
echo "isNavAllowed check: ";
var_dump(isNavAllowed('create-login-account', $allowedMenus));

echo "\n--- RENDERED HTML ---\n";
echo $html;
