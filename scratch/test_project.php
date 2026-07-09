<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

$_GET['project_id'] = 211;

echo "=== MOCKING PROJECT 211 PAGE LOAD ===\n";
require_once dirname(__DIR__) . '/submission-manager.php';
echo "\n=== PAGE LOAD COMPLETED SUCCESSFULLY ===\n";
