<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/selenium/selenium-bridge.php';
require_once dirname(__DIR__) . '/ai-content.php';

$projectId = 211; // Project ID
$platform  = 'symbaloo';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM social_accounts WHERE project_id=? AND platform=?");
$stmt->execute([$projectId, $platform]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($accounts)) {
    die("Error: No Symbaloo accounts found for Project ID {$projectId}\n");
}

echo "Found " . count($accounts) . " Symbaloo accounts in DB.\n\n";

// Target site and keyword
$keyword = "Best Web Design Agency";
$site    = "https://skyranksolution-bice.vercel.app/services";

foreach ($accounts as $idx => $creds) {
    echo "----------------------------------------\n";
    echo "Testing Account #" . ($idx + 1) . ": " . $creds['username'] . " (Project ID: " . $creds['project_id'] . ")\n";
    echo "Custom Webmix URL: " . ($creds['api_key'] ?: 'None (Default)') . "\n";
    
    // Generate AI content first (simulating the live system)
    $ai = generateAIContent($keyword, $site, 'symbaloo', 'micro_blog', '', OPENAI_API_KEY, 1, [], 'SkyRank Solution', 'AI-powered SEO agency');
    $aiDesc = $ai['content'] ?? '';
    // Convert HTML links to Anchor (URL) format and strip other tags
    $aiDesc = preg_replace('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/i', '$2 ($1)', $aiDesc);
    $aiDesc = strip_tags($aiDesc);
    $aiDesc = html_entity_decode($aiDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $aiDesc = trim(preg_replace("/\s+/", " ", $aiDesc));
    echo "Generated AI Description: " . substr($aiDesc, 0, 80) . "...\n";
    echo "Running seleniumSymbaloo...\n";
    
    $res = seleniumSymbaloo($creds, $keyword, $site, $aiDesc);
    print_r($res);
    echo "\n";
}
