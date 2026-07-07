<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

// Get the first user ID
$userStmt = $db->query("SELECT id FROM users LIMIT 1");
$user = $userStmt->fetch();
$userId = $user ? $user['id'] : 1;

$platform = 'tumblr';
$username = 'skyrankseo.tumblr.com';
$apiKey = '0bZqdLtRzavMR5m625q6kYofTl8RBsf6qhjdVonpvygScbCtyj';
$apiSecret = 'Oa6B06dJLamcdyG3utNnK9SatAz0Nsz0kwauYM3cwR2ZdaePhK';
$oauthToken = 'HvJJdfLO6KqTvQWttR71JYIIrFHmZYZJ9N4VMtyLBSEtLxEDXK';
$oauthTokenSecret = 'Bd6NxaN06zE8eITIXc1NRKPDDqm8IkcHfKiLAvrm4R3DC0tPD6';

$encryptedPassword = base64_encode($oauthToken . ':' . $oauthTokenSecret);

// 1. Update/Insert Global credentials (project_id = 0)
$check = $db->prepare("SELECT id FROM social_accounts WHERE user_id=? AND platform=? AND project_id=0");
$check->execute([$userId, $platform]);
if ($check->fetch()) {
    $db->prepare("UPDATE social_accounts SET username=?, password=?, api_key=?, api_secret=?, status='active' WHERE user_id=? AND platform=? AND project_id=0")
       ->execute([$username, $encryptedPassword, $apiKey, $apiSecret, $userId, $platform]);
    echo "Global Tumblr credentials updated successfully!\n";
} else {
    $db->prepare("INSERT INTO social_accounts (user_id, project_id, platform, username, password, api_key, api_secret, status) VALUES (?, 0, ?, ?, ?, ?, ?, 'active')")
       ->execute([$userId, $platform, $username, $encryptedPassword, $apiKey, $apiSecret]);
    echo "Global Tumblr credentials inserted successfully!\n";
}

// 2. Also update/insert project-specific credentials for all projects
$projectsStmt = $db->query("SELECT id FROM projects");
$projects = $projectsStmt->fetchAll();

foreach ($projects as $proj) {
    $pId = $proj['id'];
    $checkProj = $db->prepare("SELECT id FROM social_accounts WHERE project_id=? AND platform=?");
    $checkProj->execute([$pId, $platform]);
    if ($checkProj->fetch()) {
        $db->prepare("UPDATE social_accounts SET username=?, password=?, api_key=?, api_secret=?, status='active' WHERE project_id=? AND platform=?")
           ->execute([$username, $encryptedPassword, $apiKey, $apiSecret, $pId, $platform]);
        echo "Project ID $pId Tumblr credentials updated successfully!\n";
    } else {
        $db->prepare("INSERT INTO social_accounts (user_id, project_id, platform, username, password, api_key, api_secret, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')")
           ->execute([$userId, $pId, $platform, $username, $encryptedPassword, $apiKey, $apiSecret]);
        echo "Project ID $pId Tumblr credentials inserted successfully!\n";
    }
}
