<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/selenium/selenium-bridge.php';

$projectId = 211;
$platform  = 'livejournal';

$db = getDB();
$stmt = $db->prepare("SELECT id, username, password FROM social_accounts WHERE project_id=? AND platform=?");
$stmt->execute([$projectId, $platform]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($accounts) . " accounts for Project 211:\n\n";
foreach ($accounts as $row) {
    $raw_pw = $row['password'];
    $decoded_pw = decodePass($raw_pw);
    echo "Username: " . $row['username'] . "\n";
    echo "Raw Password: " . $raw_pw . "\n";
    echo "Decoded Password: " . $decoded_pw . "\n\n";
}
