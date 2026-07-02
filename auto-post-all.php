<?php
/**
 * Auto-post to ALL platform accounts in parallel (curl_multi).
 * Supports single platform filter or all platforms.
 */
require_once 'config.php';
requireLogin();
set_time_limit(600);
ini_set('memory_limit', '256M');

$db        = getDB();
$projectId = (int)($_GET['id'] ?? 0);
$userId    = (int)$_SESSION['user_id'];

$stmt = $db->prepare('SELECT * FROM projects WHERE id=? AND user_id=?');
$stmt->execute([$projectId, $userId]);
$project = $stmt->fetch();

if (!$project) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Project not found']);
    exit;
}

require_once __DIR__ . '/auto-poster.php';

$platformFilter = $_GET['platform'] ?? null;
if ($platformFilter) {
    $accountsStmt = $db->prepare('SELECT * FROM social_accounts WHERE project_id=? AND platform=? AND status="active" ORDER BY id');
    $accountsStmt->execute([$projectId, $platformFilter]);
} else {
    $accountsStmt = $db->prepare('SELECT * FROM social_accounts WHERE project_id=? AND status="active" ORDER BY platform, id');
    $accountsStmt->execute([$projectId]);
}
$accounts = $accountsStmt->fetchAll();

if (empty($accounts)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No credentials saved.', 'results' => []]);
    exit;
}

$results     = [];
$posted      = 0;
$skipped     = 0;
$failed      = 0;
$batchSize   = 10; // parallel batch size

// ── For Bluesky (and other platforms that support parallel) ──
// Group by platform
$byPlatform = [];
foreach ($accounts as $acc) {
    $byPlatform[$acc['platform']][] = $acc;
}

// ── Process each platform's accounts in parallel batches ──
foreach ($byPlatform as $platform => $platformAccounts) {

    // (No skip for already-posted — allow re-posting with new unique content variation)

    // Split into batches of $batchSize for parallel execution
    $batches = array_chunk($platformAccounts, $batchSize);

    foreach ($batches as $batch) {
        // Build curl_multi handles for this batch
        $mh      = curl_multi_init();
        $handles = [];

        foreach ($batch as $idx => $creds) {
            // Each account posts in its own PHP request to avoid state conflicts
            $url = SITE_URL . '/auto-poster.php?' . http_build_query([
                'id'          => $projectId,
                'platform'    => $platform,
                '_account'    => $creds['id'], // pass specific account ID
                'keyword'     => $_GET['keyword'] ?? '', // Forward custom keyword if selected
                'target_site' => $_GET['target_site'] ?? '', // Forward custom target site if selected
            ]);

            $ch = curl_init($url);

            // Build cookie string from current session
            $cookieStr = '';
            foreach ($_COOKIE as $ck => $cv) {
                $cookieStr .= urlencode($ck) . '=' . urlencode($cv) . '; ';
            }
            // Always pass session cookie
            $sessionName = session_name();
            $sessionId   = session_id();
            if ($sessionId && strpos($cookieStr, $sessionName) === false) {
                $cookieStr .= urlencode($sessionName) . '=' . urlencode($sessionId) . '; ';
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_COOKIE         => rtrim($cookieStr, '; '),
                CURLOPT_HTTPHEADER     => [
                    'X-Internal: 1',
                    'Accept: application/json',
                ],
            ]);
            $handles[$idx] = ['ch' => $ch, 'creds' => $creds];
            curl_multi_add_handle($mh, $ch);
        }

        // Execute all in parallel
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 1.0);
        } while ($running > 0);

        // Collect results
        foreach ($handles as $idx => $info) {
            $ch    = $info['ch'];
            $creds = $info['creds'];
            $body  = curl_multi_getcontent($ch);
            $http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $data = json_decode($body, true);

            if (!empty($data['success'])) {
                savePostedBacklink($db, $projectId, $platform, $data);
                $results[] = [
                    'platform' => $platform,
                    'name'     => ucfirst($platform) . ' (' . $creds['username'] . ')',
                    'handle'   => $creds['username'],
                    'status'   => 'success',
                    'url'      => $data['url'] ?? '',
                ];
                $posted++;
            } elseif (!empty($data['manual'])) {
                $results[] = [
                    'platform' => $platform,
                    'name'     => ucfirst($platform) . ' (' . $creds['username'] . ')',
                    'handle'   => $creds['username'],
                    'status'   => 'manual',
                    'message'  => 'Content ready — paste manually',
                ];
                $skipped++;
            } else {
                $results[] = [
                    'platform' => $platform,
                    'name'     => ucfirst($platform) . ' (' . $creds['username'] . ')',
                    'handle'   => $creds['username'],
                    'status'   => 'error',
                    'message'  => $data['error'] ?? "HTTP $http",
                ];
                $failed++;
            }
        }

        curl_multi_close($mh);
    }
}

header('Content-Type: application/json');
echo json_encode([
    'message' => "Done: {$posted} posted, {$skipped} skipped/manual, {$failed} failed",
    'posted'  => $posted,
    'skipped' => $skipped,
    'failed'  => $failed,
    'results' => $results,
]);
