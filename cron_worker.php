<?php
/**
 * Cron Worker - Process backlink queue tasks sequentially.
 * Executed via server cron: * * * * * php /var/www/html/cron_worker.php
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ai-content.php';
require_once __DIR__ . '/auto-poster.php';

// Prevent concurrent runs: check if any task is already processing
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(*) FROM backlink_queue WHERE status = 'processing'");
$stmt->execute();
$running = (int)$stmt->fetchColumn();

if ($running > 0) {
    // Timeout check: if a task is stuck in 'processing' status for more than 15 minutes, mark as failed
    $db->exec("UPDATE backlink_queue SET status = 'failed', error_message = 'Timeout: Process hung or was terminated by the OS.' WHERE status = 'processing' AND updated_at < NOW() - INTERVAL 15 MINUTE");
    
    echo "A task is already processing. Exiting to avoid concurrency.\n";
    exit;
}

// Get the batch size from config or default to 5
$batchSize = defined('CRON_BATCH_SIZE') ? (int)CRON_BATCH_SIZE : 5;
if ($batchSize < 1) {
    $batchSize = 1;
}

// Get oldest pending tasks up to the batch limit
$stmt = $db->prepare("SELECT * FROM backlink_queue WHERE status = 'pending' ORDER BY id ASC LIMIT ?");
$stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tasks)) {
    echo "No pending tasks in queue. Exiting.\n";
    exit;
}

echo "Found " . count($tasks) . " tasks to process in this batch.\n";

foreach ($tasks as $task) {
    $taskId = $task['id'];
    $projectId = $task['project_id'];
    $socialAccountId = $task['social_account_id'];
    $platform = $task['platform'];
    
    echo "----------------------------------------\n";
    echo "Processing Task ID {$taskId} (Platform: {$platform}, Project ID: {$projectId})\n";
    
    // Update status to processing
    $updateStmt = $db->prepare("UPDATE backlink_queue SET status = 'processing', updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$taskId]);
    
    try {
        // Load social account credentials
        $accStmt = $db->prepare("SELECT * FROM social_accounts WHERE id = ?");
        $accStmt->execute([$socialAccountId]);
        $creds = $accStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$creds) {
            throw new Exception("Social credentials not found for Account ID {$socialAccountId}");
        }
        
        // Load project details
        $projStmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $projStmt->execute([$projectId]);
        $project = $projStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            throw new Exception("Project details not found for Project ID {$projectId}");
        }
        
        // Set GET parameters so runPlatformAutoPost() picks them up
        $_GET['keyword']     = $task['keyword'];
        $_GET['target_site'] = $task['target_url'];
        
        // Execute posting
        $result = runPlatformAutoPost($platform, $creds, $project, $projectId);
        
        if (!empty($result['success'])) {
            // Save posted backlink to database
            savePostedBacklink($db, $projectId, $platform, $result);
            
            // Update task status to success
            $finishStmt = $db->prepare("UPDATE backlink_queue SET status = 'success', published_url = ?, error_message = NULL, updated_at = NOW() WHERE id = ?");
            $finishStmt->execute([$result['url'] ?? '', $taskId]);
            echo "SUCCESS: Post published successfully at " . ($result['url'] ?? '') . "\n";
            
        } elseif (!empty($result['manual'])) {
            $msg = "Manual action required. " . ($result['message'] ?? '');
            $finishStmt = $db->prepare("UPDATE backlink_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?");
            $finishStmt->execute([$msg, $taskId]);
            echo "MANUAL: " . $msg . "\n";
            
        } else {
            $err = $result['error'] ?? 'Unknown execution error occurred.';
            throw new Exception($err);
        }
        
    } catch (Throwable $e) {
        $errorMsg = $e->getMessage();
        echo "ERROR: {$errorMsg}\n";
        
        $failStmt = $db->prepare("UPDATE backlink_queue SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?");
        $failStmt->execute([$errorMsg, $taskId]);
    }
}

echo "Batch processing finished.\n";
