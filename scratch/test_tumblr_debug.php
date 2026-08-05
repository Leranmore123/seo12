<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id = 242 AND platform = 'tumblr'");
$stmt->execute();
$creds = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== Social Account DB Row for Project #242 ===\n";
print_r($creds);

if ($creds) {
    echo "\n=== Extracting tokens from DB row ===\n";
    $rawPass = $creds['password'] ?? '';
    $decrypted = base64_decode($rawPass, true);
    if ($decrypted === false || strpos($decrypted, ':') === false) {
        $decrypted = $rawPass;
    }
    $parts = explode(':', $decrypted);
    $token = trim($parts[0] ?? '');
    $tsecret = trim($parts[1] ?? '');

    echo "Blog: '{$creds['username']}'\n";
    echo "API Key: '{$creds['api_key']}'\n";
    echo "API Secret: '{$creds['api_secret']}'\n";
    echo "Decrypted Pass: '{$decrypted}'\n";
    echo "Extracted Token: '{$token}'\n";
    echo "Extracted Token Secret: '{$tsecret}'\n\n";

    echo "=== Running postToTumblr for Project #242 ===\n";
    $projStmt = $db->prepare("SELECT * FROM projects WHERE id = 242");
    $projStmt->execute();
    $project = $projStmt->fetch(PDO::FETCH_ASSOC);

    $res = runPlatformAutoPost('tumblr', $creds, $project, 242);
    print_r($res);
}
