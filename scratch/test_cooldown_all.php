<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/config.php';

header('Content-Type: text/plain');

echo "=== DIAGNOSTIC: TESTING COOLDOWN FOR ALL PLATFORMS ===\n\n";

// Mock submission-manager's function here so we don't need to load the whole HTML
function checkPlatformCooldownTest($db, $projectId, $platform, $keyword, $targetUrl, $activeAccountsCount = 1) {
    if ($activeAccountsCount < 1) {
        $activeAccountsCount = 1;
    }
    $cooldownPeriod = 43200; 

    echo "  Running query for platform: '$platform'...\n";
    
    $blCheck = $db->prepare("
        SELECT created_at FROM backlinks 
        WHERE project_id = ? 
          AND platform = ? 
          AND status = 'created' 
          AND (keyword = ? OR (keyword IS NULL AND (post_title LIKE ? OR backlink_url LIKE ?)))
          AND (target_url = ? OR target_url IS NULL)
          AND created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
        ORDER BY created_at DESC
    ");
    
    $blCheck->execute([
        $projectId, 
        $platform, 
        $keyword, 
        '%' . $keyword . '%', 
        '%' . $keyword . '%', 
        $targetUrl
    ]);
    
    $recentPosts = $blCheck->fetchAll(PDO::FETCH_ASSOC);
    echo "  Found " . count($recentPosts) . " recent posts.\n";

    if (count($recentPosts) >= $activeAccountsCount) {
        echo "  Recent posts count meets/exceeds active accounts count ($activeAccountsCount).\n";
        $oldestRecentPost = end($recentPosts);
        echo "  Oldest post created_at: " . var_export($oldestRecentPost, true) . "\n";
        
        if (!$oldestRecentPost) {
            echo "  WARNING: oldestRecentPost is empty/false!\n";
            return ['is_cooldown' => false];
        }
        
        if (!isset($oldestRecentPost['created_at'])) {
            echo "  WARNING: created_at key is missing from oldestRecentPost!\n";
            return ['is_cooldown' => false];
        }
        
        $lastPostTime = strtotime($oldestRecentPost['created_at']);
        $elapsed = time() - $lastPostTime;
        if ($elapsed < $cooldownPeriod) {
            $remaining = $cooldownPeriod - $elapsed;
            $hours = floor($remaining / 3600);
            $minutes = floor(($remaining % 3600) / 60);
            $timeString = ($hours > 0 ? "{$hours}h " : "") . "{$minutes}m";
            return [
                'is_cooldown' => true,
                'remaining' => $remaining,
                'time_str' => $timeString
            ];
        }
    }
    return [
        'is_cooldown' => false,
        'remaining' => 0,
        'time_str' => ''
    ];
}

try {
    $db = getDB();
    $projectId = 211;
    $keyword = 'AI powered SEO agency';
    $targetUrl = 'https://skyranksolution-bice.vercel.app/services';
    
    // List of platforms
    $primaryIds = ['pinterest', 'bluesky', 'mastodon', 'minds', 'symbaloo', 'devto', 'livejournal', 'blogger', 'tumblr', 'github'];
    
    // Get active accounts count mapping
    $savedAccounts = $db->prepare("SELECT platform FROM social_accounts WHERE project_id=?");
    $savedAccounts->execute([$projectId]);
    $accounts = $savedAccounts->fetchAll(PDO::FETCH_ASSOC);
    $counts = [];
    foreach ($accounts as $acc) {
        $counts[$acc['platform']] = ($counts[$acc['platform']] ?? 0) + 1;
    }
    
    foreach ($primaryIds as $platform) {
        echo "Platform: $platform\n";
        $activeCount = $counts[$platform] ?? 0;
        echo "  Active accounts count: $activeCount\n";
        
        $res = checkPlatformCooldownTest($db, $projectId, $platform, $keyword, $targetUrl, $activeCount);
        echo "  Result: " . json_encode($res) . "\n\n";
    }
    
    echo "=== ALL PLATFORMS TESTED SUCCESSFULLY ===\n";
} catch (Throwable $t) {
    echo "FATAL EXCEPTION: " . $t->getMessage() . "\n";
    echo "Trace:\n" . $t->getTraceAsString() . "\n";
}
