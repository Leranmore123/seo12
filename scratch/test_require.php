<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require_once dirname(__DIR__) . '/submission-manager.php';
