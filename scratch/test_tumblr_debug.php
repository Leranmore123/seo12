<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

echo "=== Projects for pd1 to pd20 Users ===\n";
echo "User ID | Username | Project ID | Project Name\n";
echo "-----------------------------------------------\n";

for ($n = 1; $n <= 20; $n++) {
    $uname = "pd{$n}";
    $uStmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $uStmt->execute([$uname]);
    $uid = $uStmt->fetchColumn();
    
    if (!$uid) {
        echo "pd{$n} => User not found\n";
        continue;
    }
    
    $pStmt = $db->prepare("SELECT id, name FROM projects WHERE user_id = ?");
    $pStmt->execute([$uid]);
    $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "pd{$n} (UID {$uid}) => NO PROJECTS FOUND\n";
    } else {
        foreach ($projects as $p) {
            echo "pd{$n} (UID {$uid}) => Project #{$p['id']} ({$p['name']})\n";
        }
    }
}
