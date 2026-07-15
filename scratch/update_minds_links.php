<?php
require_once __DIR__ . '/../config.php';

try {
    $db = getDB();
    // Fetch all Minds social accounts
    $stmt = $db->query("SELECT project_id, username FROM social_accounts WHERE platform='minds'");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== UPDATING MINDS BACKLINKS IN DATABASE ===\n";
    foreach ($accounts as $acc) {
        $user = $acc['username'];
        $projId = $acc['project_id'];
        $newUrl = "https://www.minds.com/" . $user;
        
        // Update all generic feed URLs to the user profile URL
        $up = $db->prepare("UPDATE backlinks SET backlink_url=? WHERE platform='minds' AND project_id=? AND backlink_url LIKE '%newsfeed%'");
        $up->execute([$newUrl, $projId]);
        
        echo "Project ID {$projId} -> Updated to: {$newUrl} (Rows affected: " . $up->rowCount() . ")\n";
    }
    echo "Done!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
