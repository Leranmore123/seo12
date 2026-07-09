<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['user_id'] = 22;
$_SESSION['role'] = 'client';
$_GET['project_id'] = 211;

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/submission-manager.php'; // Loads functions

header('Content-Type: text/plain');

echo "=== TESTING checkPlatformCooldown FOR DEVTO ===\n";
try {
    $db = getDB();
    $projectId = 211;
    $platform = 'devto';
    $keyword = 'AI powered SEO agency';
    $targetUrl = 'https://skyranksolution-bice.vercel.app/services';
    $activeAccountsCount = 1;
    
    $cooldown = checkPlatformCooldown($db, $projectId, $platform, $keyword, $targetUrl, $activeAccountsCount);
    echo "Cooldown Result:\n";
    print_r($cooldown);
    echo "\n=== checkPlatformCooldown RAN SUCCESSFULLY ===\n";
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
} catch (Throwable $t) {
    echo "Caught Fatal Error: " . $t->getMessage() . "\n";
}
exit;
