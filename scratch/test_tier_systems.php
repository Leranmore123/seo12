<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/auto-poster.php';

header('Content-Type: text/plain');

echo "=== TESTING ANCHOR TEXT DIVERSITY GENERATOR ===\n";

$keyword = "SEO Services Kalyan Nagar";
$businessName = "SkyRank Solution";
$url = "https://skyranksolution.com";

// Test multiple iterations to see random rotation
for ($i = 1; $i <= 10; $i++) {
    $anchor = getRandomAnchorText($keyword, $businessName, $url);
    echo "Iteration $i: '$anchor'\n";
}

echo "\n--- Test getDiverseAnchorTexts() ---\n";
$anchors = getDiverseAnchorTexts($keyword, $businessName, $url);
print_r($anchors);


echo "\n=== TESTING TIER 2 PYRAMID ENQUEUER ===\n";
try {
    $db = getDB();
    
    // Find an active project
    $project = $db->query("SELECT id FROM projects LIMIT 1")->fetch();
    if ($project) {
        $projId = (int)$project['id'];
        echo "Testing with Project ID: $projId\n";
        
        // Count queue items before test
        $before = (int)$db->query("SELECT COUNT(*) FROM backlink_queue WHERE status = 'pending'")->fetchColumn();
        echo "Queue size before: $before\n";
        
        // Trigger simulated WordPress success post
        $simulatedResult = [
            'success' => true,
            'url' => 'https://simulatedblog.wordpress.com/2026/07/09/test-post',
            'post_title' => 'Simulated SEO Post Guide'
        ];
        
        savePostedBacklink($db, $projId, 'wordpress', $simulatedResult);
        
        // Count queue items after test
        $after = (int)$db->query("SELECT COUNT(*) FROM backlink_queue WHERE status = 'pending'")->fetchColumn();
        echo "Queue size after: $after\n";
        
        $added = $after - $before;
        echo "Successfully queued $added Tier 2 backlinks pointing to '{$simulatedResult['url']}'!\n";
        
        // Output new queue items for inspection
        $items = $db->query("SELECT platform, keyword, target_url FROM backlink_queue WHERE target_url = '{$simulatedResult['url']}'")->fetchAll(PDO::FETCH_ASSOC);
        print_r($items);
        
        // Clean up the test backlinks and queue items
        $db->prepare("DELETE FROM backlinks WHERE backlink_url = ?")->execute([$simulatedResult['url']]);
        $db->prepare("DELETE FROM backlink_queue WHERE target_url = ?")->execute([$simulatedResult['url']]);
        echo "\nClean-up completed.\n";
        
    } else {
        echo "No projects found in database to run integration test.\n";
    }
} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
}
