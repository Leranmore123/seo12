<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

$workingAccount = [
    'blog'    => 'howtoverifypropertyingujara',
    'key'     => 'QRWO9l1KifJyiBJICcvOkHS5J0XM4UT3AzdjRdeWocev3mEb8z',
    'secret'  => 'C0lFnqNOncJ5zbCw3fFLjQxpBW4EiefYfWBR6mWOe3Kc7n8XYa',
    'token'   => '9vldaudhMnkJt9FkTpDF6MQ6i7d7s86imqAnpylT5zEQBuowP1',
    'tsecret' => 'l13CukH4Obp1PBWXchWl6cj6qrMEp3l4UvxECdo5LaZ9NFok3r'
];

$pass = base64_encode($workingAccount['token'] . ':' . $workingAccount['tsecret']);

echo "=== Syncing Active Working Tumblr Credentials Across All Projects ===\n\n";

// Update all existing tumblr social accounts in the DB
$stmt = $db->prepare("UPDATE social_accounts SET username = ?, api_key = ?, api_secret = ?, password = ?, status = 'active' WHERE platform = 'tumblr'");
$stmt->execute([
    $workingAccount['blog'],
    $workingAccount['key'],
    $workingAccount['secret'],
    $pass
]);

$count = $stmt->rowCount();
echo "🎉 SUCCESS: Updated {$count} Tumblr social account rows in Database to active blog '{$workingAccount['blog']}'!\n";
