<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Include submission-manager.php to get the $platforms array
// We buffer the output so it doesn't print the HTML page yet
ob_start();
require_once dirname(__DIR__) . '/submission-manager.php';
ob_end_clean();

header('Content-Type: text/plain');
echo "=== TESTING PLATFORMS DATA STRUCTURE ===\n";
echo "Platforms count: " . count($platforms) . "\n\n";

foreach ($platforms as $catKey => $cat) {
    echo "Category: $catKey (Title: " . ($cat['title'] ?? 'N/A') . ")\n";
    if (empty($cat['sites'])) {
        echo "  ERROR: 'sites' array is empty or missing!\n";
        continue;
    }
    foreach ($cat['sites'] as $idx => $site) {
        $id = $site['id'] ?? 'MISSING_ID';
        $name = $site['name'] ?? 'MISSING_NAME';
        echo "  [$idx] Site: $id ($name)\n";
        if (!isset($site['what_system_does'])) {
            echo "    WARNING: 'what_system_does' is NOT set!\n";
        } else {
            echo "    what_system_does: " . substr($site['what_system_does'], 0, 80) . "...\n";
        }
    }
    echo "\n";
}
echo "=== LOOP FINISHED SUCCESSFULLY ===\n";
