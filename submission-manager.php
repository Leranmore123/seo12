<?php
require_once 'config.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];

try {
    $db->exec("ALTER TABLE social_accounts ADD COLUMN project_id INT NOT NULL DEFAULT 0");
    $db->exec("ALTER TABLE social_accounts DROP INDEX user_platform");
} catch (PDOException $e) {}
try {
    $db->exec("ALTER TABLE social_accounts DROP INDEX project_platform");
} catch (PDOException $e) {}
try {
    $db->exec("ALTER TABLE backlinks ADD COLUMN verified_status VARCHAR(50) DEFAULT 'unverified'");
} catch (PDOException $e) {}
try {
    $db->exec("ALTER TABLE backlinks ADD COLUMN last_checked_at DATETIME DEFAULT NULL");
} catch (PDOException $e) {}

// Handle AJAX keyword / target site url updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project_targets'])) {
    header('Content-Type: application/json');
    $pId = (int)($_POST['project_id'] ?? 0);
    $targetKeywords = trim($_POST['target_keywords'] ?? '');
    $targetSites = trim($_POST['target_sites'] ?? '');

    $upd = $db->prepare("UPDATE projects SET target_keyword = ?, target_site = ? WHERE id = ? AND user_id = ?");
    $upd->execute([$targetKeywords, $targetSites, $pId, $userId]);

    echo json_encode(['success' => true]);
    exit;
}


// ============================================================
// WordPress.com OAuth2 Callback — auto-save token
// URL: http://localhost/seo-system/submission-manager.php?wp_oauth=1&code=XXX
// ============================================================
if (isset($_GET['wp_oauth']) && isset($_GET['code'])) {
    $code         = clean($_GET['code']);
    $clientId     = '138093';
    $clientSecret = 'vJABVPp0TCW05oBjjfpXqgaeVH9PdvN3X0f53DlLFuHYyZtKuvibvTwzB4x795Ew';
    $redirectUri  = SITE_URL . '/submission-manager.php';

    // Exchange code for access token
    $ch = curl_init('https://public-api.wordpress.com/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);

    if (!empty($data['access_token'])) {
        $token    = $data['access_token'];
        $blogUrl  = $data['blog_url'] ?? '';
        $wpSite   = str_replace(['https://', 'http://'], '', rtrim($blogUrl, '/'));

        // Save to social_accounts
        $check = $db->prepare("SELECT id FROM social_accounts WHERE user_id=? AND platform='wordpress'");
        $check->execute([$userId]);
        if ($check->fetch()) {
            $db->prepare("UPDATE social_accounts SET api_key=?, username=? WHERE user_id=? AND platform='wordpress'")
               ->execute([$token, $wpSite, $userId]);
        } else {
            $db->prepare("INSERT INTO social_accounts (user_id, platform, username, api_key, status) VALUES (?,?,?,?,'active')")
               ->execute([$userId, 'wordpress', $wpSite, $token]);
        }
        setFlash('success', '✅ WordPress.com connected! Token saved automatically. Site: ' . $wpSite);
    } else {
        $err = $data['error_description'] ?? $data['error'] ?? 'Unknown error';
        setFlash('danger', '❌ WordPress OAuth failed: ' . $err);
    }
    header('Location: submission-manager.php'); exit;
}

// WordPress AJAX Connect — email+password → token → save → test post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_ajax_connect'])) {
    header('Content-Type: application/json');

    $clientId     = '141079';
    $clientSecret = 'V4vTd95NVEIBHNYfFXLBUgCRSa70CrcWiPaLdKRuOOCV3VltBz3nvsG2wVLFVOAV';
    $wpEmail      = trim($_POST['wp_email'] ?? '');
    $wpPassword   = $_POST['wp_password'] ?? '';

    if (empty($wpEmail) || empty($wpPassword)) {
        echo json_encode(['error' => 'Email અને Password required']);
        exit;
    }

    // Step 1: Get token
    $ch = curl_init('https://public-api.wordpress.com/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'password',
            'username'      => $wpEmail,
            'password'      => $wpPassword,
        ]),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $tokenData = json_decode($resp, true);

    if (empty($tokenData['access_token'])) {
        echo json_encode(['error' => $tokenData['error_description'] ?? $tokenData['error'] ?? 'Token failed']);
        exit;
    }
    $token = $tokenData['access_token'];

    // Step 2: Get sites
    $ch2 = curl_init('https://public-api.wordpress.com/rest/v1.1/me/sites?fields=ID,URL,name');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $sitesResp = curl_exec($ch2);
    curl_close($ch2);
    $sitesData = json_decode($sitesResp, true);
    $sites     = $sitesData['sites'] ?? [];

    // Fallback: get primary blog from /me
    if (empty($sites)) {
        $ch3 = curl_init('https://public-api.wordpress.com/rest/v1.1/me');
        curl_setopt_array($ch3, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $meData    = json_decode(curl_exec($ch3), true);
        curl_close($ch3);
        $primaryId  = $meData['primary_blog'] ?? null;
        $primaryUrl = $meData['primary_blog_url'] ?? null;
        if ($primaryId) {
            $sites = [['ID' => $primaryId, 'URL' => $primaryUrl, 'name' => 'Primary Blog']];
        }
    }

    if (empty($sites)) {
        echo json_encode(['error' => 'No WordPress site found for this account']);
        exit;
    }

    $siteId     = $sites[0]['ID'];
    $siteDomain = str_replace(['https://','http://'], '', rtrim($sites[0]['URL'] ?? '', '/'));

    // Step 3: Save token to DB
    $check = $db->prepare("SELECT id FROM social_accounts WHERE user_id=? AND platform='wordpress'");
    $check->execute([$userId]);
    if ($check->fetch()) {
        $db->prepare("UPDATE social_accounts SET api_key=?, username=?, status='active' WHERE user_id=? AND platform='wordpress'")
           ->execute([$token, $siteDomain, $userId]);
    } else {
        $db->prepare("INSERT INTO social_accounts (user_id,platform,username,api_key,status) VALUES (?,?,?,?,'active')")
           ->execute([$userId, 'wordpress', $siteDomain, $token]);
    }

    // Step 4: Test post
    $postUrl = null;
    $ch4 = curl_init("https://public-api.wordpress.com/rest/v1.1/sites/{$siteId}/posts/new");
    curl_setopt_array($ch4, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'title'   => 'SEO Guide - LearnMore Technologies ' . date('Y'),
            'content' => '<h1>Best Training in Bangalore</h1><p>LearnMore Technologies offers industry-best training programs. Visit <a href="https://learnmoretech.in">learnmoretech.in</a> to enroll today.</p>',
            'status'  => 'publish',
            'tags'    => 'training,seo,learnmore,bangalore',
        ]),
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $postResp   = curl_exec($ch4);
    $postCode   = (int)curl_getinfo($ch4, CURLINFO_HTTP_CODE);
    curl_close($ch4);
    $postResult = json_decode($postResp, true);
    $postUrl    = $postResult['URL'] ?? null;

    echo json_encode([
        'success'  => true,
        'site'     => $siteDomain,
        'site_id'  => $siteId,
        'token'    => substr($token, 0, 10) . '...',
        'post_url' => $postUrl,
        'message'  => 'WordPress connected! Token saved.',
    ]);
    exit;
}

// WordPress OAuth — start flow
if (isset($_GET['wp_connect'])) {
    $clientId    = '138093';
    $redirectUri = SITE_URL . '/submission-manager.php';
    $authUrl = 'https://public-api.wordpress.com/oauth2/authorize?' . http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'global',
    ]);
    header('Location: ' . $authUrl); exit;
}

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo'])) {
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === 0) {
        $assetsDir = __DIR__ . '/assets/';
        if (!is_dir($assetsDir)) mkdir($assetsDir, 0755, true);
        $ext     = strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            move_uploaded_file($_FILES['logo_image']['tmp_name'], $assetsDir . 'logo.' . $ext);
            // Save extension to config file
            file_put_contents($assetsDir . 'logo_ext.txt', $ext);
            setFlash('success', 'Logo uploaded! Will be used in all generated images.');
        } else {
            setFlash('danger', 'Invalid file. Use PNG (recommended), JPG, GIF or WebP.');
        }
    }
    header('Location: submission-manager.php?project_id=' . ($_POST['project_id'] ?? '')); exit;
}

// Handle image upload for project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $projectId = (int)$_POST['project_id'];
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext      = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'project_' . $projectId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadDir . $filename);
            $db->prepare("UPDATE projects SET post_image=? WHERE id=? AND user_id=?")
               ->execute([$filename, $projectId, $userId]);
            setFlash('success', 'Image uploaded! System will use this image for all posts.');
        } else {
            setFlash('danger', 'Invalid file type. Use JPG, PNG, GIF or WebP.');
        }
    }
    header('Location: submission-manager.php?project_id=' . $projectId); exit;
}

// Fetch all projects for this user
$projects = $db->prepare("SELECT * FROM projects WHERE user_id=? ORDER BY created_at DESC");
$projects->execute([$userId]);
$projects = $projects->fetchAll();

$flash = getFlash();

// Handle save platform credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_platform'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.'); header('Location: submission-manager.php'); exit;
    }
    $platform  = clean($_POST['platform']);
    $username  = clean($_POST['username']);
    $password  = $_POST['password'];
    $apiKey    = clean($_POST['api_key'] ?? '');
    $apiSecret = clean($_POST['api_secret'] ?? '');
    $projectId = (int)$_POST['project_id'];

    // Mastodon: username field = instance (mastodon.social)
    // email goes to api_secret for Selenium use
    if ($platform === 'mastodon') {
        $email = $username; // entered email
        if (strpos($email, '@') !== false) {
            // User entered email — store properly
            $username  = 'mastodon.social'; // instance
            $apiSecret = $email;            // email for Selenium
        }
    }

    $check = $db->prepare("SELECT id FROM social_accounts WHERE project_id=? AND platform=? AND username=?");
    $check->execute([$projectId, $platform, $username]);
    $existing = $check->fetch();
    if ($existing) {
        $db->prepare("UPDATE social_accounts SET password=?, api_key=?, api_secret=?, user_id=? WHERE id=?")
           ->execute([base64_encode($password), $apiKey, $apiSecret, $userId, $existing['id']]);
    } else {
        $db->prepare("INSERT INTO social_accounts (user_id, project_id, platform, username, password, api_key, api_secret) VALUES (?,?,?,?,?,?,?)")
           ->execute([$userId, $projectId, $platform, $username, base64_encode($password), $apiKey, $apiSecret]);
    }
    setFlash('success', ucfirst($platform) . ' credentials saved! System will use these to post.');
    header('Location: submission-manager.php?project_id=' . $projectId); exit;
}

// Handle delete account
if (isset($_GET['delete_account'])) {
    $accId = (int)$_GET['delete_account'];
    $db->prepare("DELETE FROM social_accounts WHERE id=? AND user_id=?")->execute([$accId, $userId]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Handle AJAX single backlink verification
if (isset($_GET['verify_backlink_ajax'])) {
    $blId = (int)$_GET['verify_backlink_ajax'];
    
    // Fetch backlink
    $stmt = $db->prepare("
        SELECT b.*, p.website_url, p.target_site 
        FROM backlinks b
        JOIN projects p ON b.project_id = p.id
        WHERE b.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$blId, $userId]);
    $bl = $stmt->fetch();
    
    if (!$bl) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Backlink not found or access denied.']);
        exit;
    }
    
    $status = verifyBacklink($bl['backlink_url'], $bl['website_url'], $bl['target_site']);
    
    $db->prepare("UPDATE backlinks SET verified_status=?, last_checked_at=NOW() WHERE id=?")
       ->execute([$status, $blId]);
       
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'status' => $status, 
        'last_checked' => date('d M, H:i')
    ]);
    exit;
}

// Handle universal bulk account add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bulk_universal'])) {
    $projectId = (int)$_POST['project_id'];
    $lines     = explode("\n", trim($_POST['bulk_accounts'] ?? ''));
    $added = 0; $errors = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        $parts = str_getcsv($line); // handle commas in values
        if (count($parts) < 3) { $errors[] = "Skipped (bad format): $line"; continue; }
        $platform  = clean(strtolower(trim($parts[0])));
        $username  = clean(trim($parts[1]));
        $password  = trim($parts[2]); // api_key or password
        $apiSecret = isset($parts[3]) ? clean(trim($parts[3])) : '';

        // For platforms using api_key (devto, github, hashnode, plurk, tumblr, wordpress)
        $apiKeyPlatforms = ['devto','github','hashnode','plurk','tumblr','wordpress','blogger','ghost','minds'];
        if (in_array($platform, $apiKeyPlatforms)) {
            $db->prepare("INSERT INTO social_accounts (user_id, platform, username, api_key, api_secret, status) VALUES (?,?,?,?,?,'active')")
               ->execute([$userId, $platform, $username, $password, $apiSecret]);
        } else {
            // username+password platforms (bluesky, mediafire, fourshared, penzu, etc.)
            $db->prepare("INSERT INTO social_accounts (user_id, platform, username, password, api_key, status) VALUES (?,?,?,?,?,'active')")
               ->execute([$userId, $platform, $username, base64_encode($password), $apiSecret]);
        }
        $added++;
    }
    $msg = "$added account(s) added!";
    if (!empty($errors)) $msg .= ' Issues: ' . implode(', ', $errors);
    setFlash('success', $msg);
    header('Location: submission-manager.php?project_id=' . $projectId); exit;
}

// Handle bulk Bluesky accounts (legacy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bulk_bluesky'])) {
    $projectId = (int)$_POST['project_id'];
    $lines = explode("\n", trim($_POST['bluesky_bulk']));
    foreach ($lines as $line) {
        $parts = explode(',', trim($line));
        if (count($parts) === 2) {
            $handle = clean($parts[0]);
            $appPass = trim($parts[1]);
            $db->prepare("INSERT IGNORE INTO social_accounts (user_id, platform, username, password) VALUES (?, 'bluesky', ?, ?)")
               ->execute([$userId, $handle, base64_encode($appPass)]);
        }
    }
    setFlash('success', 'Bulk accounts processed!');
    header('Location: submission-manager.php?project_id=' . $projectId); exit;
}

$selectedProjectId = (int)($_GET['project_id'] ?? ($projects[0]['id'] ?? 0));
$project = null;
foreach ($projects as $p) {
    if ($p['id'] == $selectedProjectId) { $project = $p; break; }
}

// Fetch saved credentials
$savedAccounts = $db->prepare("SELECT * FROM social_accounts WHERE project_id=?");
$savedAccounts->execute([$selectedProjectId]);
$savedAccounts = $savedAccounts->fetchAll();
// savedMap: platform => first account (for backward compat)
// savedMapAll: platform => array of all accounts
$savedMap    = [];
$savedMapAll = [];
foreach ($savedAccounts as $acc) {
    if (!isset($savedMap[$acc['platform']])) {
        $savedMap[$acc['platform']] = $acc;
    }
    $savedMapAll[$acc['platform']][] = $acc;
}

// Platform list with what system does automatically
$platforms = [
    'profile_creation' => [
        'title' => '👤 Profile Creation Sites',
        'color' => 'primary',
        'sites' => [
            ['id' => 'google_business', 'name' => 'Google Business Profile', 'url' => 'https://business.google.com', 'what_system_does' => '🤖 Browser Automation — Google Email (Username). System Chrome reads master profile chrome_profile_gsc, goes to Business Search dashboard, and posts updates with text + image automatically!'],
            ['id' => 'pinterest',    'name' => 'Pinterest',      'url' => 'https://www.pinterest.com',   'what_system_does' => '🤖 Browser Automation — Email + Password save karo. System Chrome kholine auto login kare + pin create kare with image + backlink'],
            ['id' => 'bluesky',      'name' => 'Bluesky',        'url' => 'https://bsky.app',            'what_system_does' => '✅ 100% Auto — AT Protocol API. Use App Password from bsky.app → Settings → App Passwords'],
            ['id' => 'mastodon',     'name' => 'Mastodon',       'url' => 'https://mastodon.social',     'what_system_does' => '🤖 Browser Automation — Email + Password save karo. System auto-login kare, app create kare, token generate kare + post kare automatically!'],
            ['id' => 'minds',        'name' => 'Minds.com',      'url' => 'https://www.minds.com',       'what_system_does' => '✅ 100% Auto — REST API. Get token: minds.com → Settings → Security → API Token'],
            ['id' => 'dribbble',     'name' => 'Dribbble',       'url' => 'https://dribbble.com',        'what_system_does' => '✅ 100% Auto — Upload shot with keyword + backlink. Get token: dribbble.com/account/applications'],
            ['id' => 'symbaloo',     'name' => 'Symbaloo',       'url' => 'https://www.symbaloo.com',    'what_system_does' => 'ChatGPT content → Copy & paste manually'],
            ['id' => 'penzu',        'name' => 'Penzu',          'url' => 'https://penzu.com',           'what_system_does' => 'ChatGPT content → Copy & paste manually'],
            ['id' => 'plurk',        'name' => 'Plurk',          'url' => 'https://www.plurk.com',       'what_system_does' => '✅ 100% Auto — REST API. Get key: plurk.com/API/Apps'],
            ['id' => 'devto',        'name' => 'Dev.to',         'url' => 'https://dev.to',              'what_system_does' => '✅ 100% Auto — API post. Get key: dev.to/settings/extensions'],
            ['id' => 'linktree',     'name' => 'Linktree',       'url' => 'https://linktr.ee',           'what_system_does' => 'ChatGPT content → Copy & paste manually (no public API)'],
        ]
    ],
    'micro_blogging' => [
        'title' => '✍️ Micro Blogging',
        'color' => 'success',
        'sites' => [
            ['id' => 'scoopit',      'name' => 'Scoop.it',       'url' => 'https://www.scoop.it',        'what_system_does' => '📋 Semi-Auto — Auto Post dabavo → Title + Description auto-generate thay → Copy karo → Scoop.it khulse → Paste karo → Publish button dabavo'],
            ['id' => 'wakelet',      'name' => 'Wakelet',        'url' => 'https://wakelet.com',         'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System collection create kare + URL add kare with keyword + backlink'],
            ['id' => 'vivauae',      'name' => 'Vivauae',        'url' => 'https://vivauae.com',         'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System article submit kare with keyword + backlink'],
            ['id' => 'padlet',       'name' => 'Padlet',         'url' => 'https://padlet.com',          'what_system_does' => '🤖 Browser Session — Chrome band karo → Auto Post click karo → System tumhara browser session use karke post add karega. Board: lmt-wb7faycbn66hp2z5'],
            ['id' => 'pearltrees',   'name' => 'Pearltrees',     'url' => 'https://www.pearltrees.com',  'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System pearl collection ma website link add kare'],
            ['id' => 'mewe',         'name' => 'MeWe',           'url' => 'https://mewe.com',            'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System auto-login kare + post kare with keyword + backlink'],
            ['id' => 'instapaper',   'name' => 'Instapaper',     'url' => 'https://www.instapaper.com',  'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System article save kare with website link'],
        ]
    ],
    'image_posting' => [
        'title' => '🖼️ Image Posting',
        'color' => 'warning',
        'sites' => [
            ['id' => 'gifyu',        'name' => 'Gifyu',          'url' => 'https://gifyu.com',           'what_system_does' => 'Image upload with keyword description + backlink'],
            ['id' => 'postimage',    'name' => 'PostImage',      'url' => 'http://www.postimage.org',    'what_system_does' => 'Image post with keyword + website link'],
            ['id' => 'photobucket',  'name' => 'Photobucket',    'url' => 'https://photobucket.com',     'what_system_does' => 'Photo album with keyword + backlink'],
            ['id' => 'behance',      'name' => 'Behance',        'url' => 'https://www.behance.net',     'what_system_does' => 'Portfolio project with keyword + website link'],
            ['id' => 'pbase',        'name' => 'Pbase',          'url' => 'https://www.pbase.com',       'what_system_does' => 'Photo gallery with keyword + backlink'],
            ['id' => 'dropbox',      'name' => 'Dropbox',        'url' => 'https://www.dropbox.com',     'what_system_does' => 'Shared folder with keyword content'],
            ['id' => 'imgbb',        'name' => 'ImgBB',          'url' => 'https://imgbb.com',           'what_system_does' => '✅ 100% Auto — Upload image with keyword + backlink. Get free API key: imgbb.com/api'],
            ['id' => 'googledrive',  'name' => 'Google Drive',   'url' => 'https://drive.google.com',    'what_system_does' => '✅ 100% Auto — Upload PDF to public folder. Get token: console.cloud.google.com → Drive API'],
        ]
    ],
    'blog_posting' => [
        'title' => '📝 Blog Posting',
        'color' => 'info',
        'sites' => [
            ['id' => 'devto',        'name' => 'Dev.to',         'url' => 'https://dev.to',              'what_system_does' => '✅ 100% Auto — API post. Get key: dev.to/settings/extensions'],
            ['id' => 'hashnode',     'name' => 'Hashnode',       'url' => 'https://hashnode.com',        'what_system_does' => '✅ 100% Auto — GraphQL API post. Get key: hashnode.com/settings/developer'],
            ['id' => 'ghost',        'name' => 'Ghost.io',       'url' => 'https://ghost.io',            'what_system_does' => '✅ 100% Auto — Admin API post. Get key: Ghost Admin → Integrations'],
            ['id' => 'site123',      'name' => 'Site123',        'url' => 'https://www.site123.com',     'what_system_does' => '🤖 Browser Automation — Email+Password save karo. System auto-login kare + blog post create kare with keyword + backlink'],
            ['id' => 'posteezy',     'name' => 'Posteezy',       'url' => 'https://www.posteezy.com',    'what_system_does' => 'Article with keyword + backlink'],
            ['id' => 'livejournal',  'name' => 'LiveJournal',    'url' => 'https://www.livejournal.com', 'what_system_does' => '🤖 Browser Automation — Username LMT_12 + Password. System auto-login + blog post create kare with keyword + backlink'],
            ['id' => 'justpaste',    'name' => 'JustPaste.it',   'url' => 'https://justpaste.it',        'what_system_does' => 'Article paste with keyword + backlink'],
            ['id' => 'wordpress',    'name' => 'WordPress.com',  'url' => 'https://wordpress.com',       'what_system_does' => '✅ 100% Auto — Click "Connect WordPress" button below to login and auto-save token'],
            ['id' => 'medium',       'name' => 'Medium.com',     'url' => 'https://medium.com',          'what_system_does' => 'ChatGPT article → Copy & paste (API discontinued)'],
            ['id' => 'substack',     'name' => 'Substack',       'url' => 'https://substack.com',        'what_system_does' => 'ChatGPT newsletter post → Copy & paste'],
            ['id' => 'blogger',      'name' => 'Blogger.com',    'url' => 'https://www.blogger.com',     'what_system_does' => '✅ 100% Auto — Google OAuth token + Blog ID'],
            ['id' => 'tumblr',       'name' => 'Tumblr',         'url' => 'https://www.tumblr.com',      'what_system_does' => '✅ 100% Auto — OAuth API. Get key: tumblr.com/oauth/apps'],
            ['id' => 'github',       'name' => 'GitHub',         'url' => 'https://github.com',          'what_system_does' => '✅ 100% Auto — Creates repo + README. Get token: github.com/settings/tokens'],
        ]
    ],
    'pdf_posting' => [
        'title' => '📄 PDF / Document Posting',
        'color' => 'danger',
        'sites' => [
            ['id' => 'mediafire',    'name' => 'MediaFire',      'url' => 'https://mediafire.com',       'what_system_does' => '✅ Auto — Logs in with Email + Password → Generates PDF with keyword description + backlink → Uploads to MediaFire → Returns public share link'],
            ['id' => 'limewire',     'name' => 'LimeWire',       'url' => 'https://limewire.com',        'what_system_does' => 'File share with keyword + backlink'],
            ['id' => 'fourshared',   'name' => '4Shared',        'url' => 'https://4shared.com',         'what_system_does' => '✅ Auto — Logs in with Email + Password → Generates PDF with keyword description + backlink → Uploads to 4Shared → Returns public share link'],
            ['id' => 'workupload',   'name' => 'WorkUpload',     'url' => 'https://workupload.com',      'what_system_does' => 'File upload with keyword description'],
            ['id' => 'powershow',    'name' => 'PowerShow',      'url' => 'https://www.powershow.com',   'what_system_does' => 'PPT presentation with keyword + backlink'],
            ['id' => 'uploadee',     'name' => 'Upload.ee',      'url' => 'https://www.upload.ee',       'what_system_does' => 'File upload with keyword + backlink'],
            ['id' => 'pdfhost',      'name' => 'PDFHost',        'url' => 'https://pdfhost.io',          'what_system_does' => 'PDF host with keyword description + backlink'],
        ]
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submission Manager - SEO 80/20</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid py-4">

  <div class="row mb-4">
    <div class="col">
      <h3><i class="fas fa-paper-plane me-2 text-primary"></i>Submission Manager</h3>
      <p class="text-muted">User credentials આપો → System automatically post કરે</p>
    </div>
    <div class="col-auto">
      <!-- Project selector -->
      <select class="form-select" onchange="location.href='submission-manager.php?project_id='+this.value">
        <?php foreach ($projects as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $p['id'] == $selectedProjectId ? 'selected' : '' ?>>
            <?= clean($p['target_keyword']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
    <?= $flash['msg'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <?php if ($project): ?>
  <?php
  $keywordsList = array_filter(array_map('trim', explode(',', $project['target_keyword'])));
  if (empty($keywordsList)) {
      $keywordsList = ['SEO Services'];
  }
  $targetSitesList = array_filter(array_map('trim', explode(',', $project['target_site'] ?: $project['website_url'])));
  if (empty($targetSitesList)) {
      $targetSitesList = [$project['website_url']];
  }
  $currentKeyword = $_GET['keyword'] ?? $keywordsList[0] ?? '';
  $currentTargetSite = $_GET['target_site'] ?? $targetSitesList[0] ?? '';
  ?>
  <!-- Keyword & Target Page URL Selectors for Auto-Post -->
  <div class="card mb-3 border-info shadow-sm bg-light">
    <div class="card-body py-2 px-3 d-flex align-items-center flex-wrap gap-3">
      <div class="d-flex align-items-center gap-2">
        <strong class="small text-muted mb-0"><i class="fas fa-key me-1"></i> Select Keyword for Auto-Post:</strong>
        <select id="backlinkKeywordSelect" class="form-select form-select-sm" style="width: auto; min-width: 250px;" onchange="updateAutoPostSelection()">
          <?php foreach ($keywordsList as $kw): ?>
            <option value="<?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?>" <?= $currentKeyword === $kw ? 'selected' : '' ?>><?= htmlspecialchars($kw) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="d-flex align-items-center gap-2">
        <strong class="small text-muted mb-0"><i class="fas fa-link me-1"></i> Select Target Page URL:</strong>
        <select id="backlinkUrlSelect" class="form-select form-select-sm" style="width: auto; min-width: 320px;" onchange="updateAutoPostSelection()">
          <?php foreach ($targetSitesList as $url): ?>
            <option value="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" <?= $currentTargetSite === $url ? 'selected' : '' ?>><?= htmlspecialchars($url) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-sm btn-outline-secondary ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#manageTargetsPanel">
        <i class="fas fa-edit me-1"></i>Edit Lists
      </button>
    </div>
  </div>

  <!-- Manage Keywords & Target URLs Collapse Panel -->
  <div class="collapse mb-4" id="manageTargetsPanel">
    <div class="card border-info shadow-sm">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Manage Keywords & Target Page URLs</h6>
        <small class="text-white-50">Changes are auto-saved to your project profile</small>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Keywords Column -->
          <div class="col-md-6 border-end">
            <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-key me-1"></i> Keywords List</h6>
            <div id="managerKeywordsContainer" class="d-flex flex-wrap gap-2 mb-3">
              <!-- rendered via JS badge list -->
            </div>
            <div class="input-group input-group-sm">
              <input type="text" id="managerKeywordInput" class="form-control" placeholder="Add new keyword">
              <button class="btn btn-primary" type="button" onclick="managerAddKeyword()">Add</button>
            </div>
          </div>
          <!-- Target Page URLs Column -->
          <div class="col-md-6">
            <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-link me-1"></i> Target Page URLs</h6>
            <div id="managerSitesContainer" class="d-flex flex-wrap gap-2 mb-3">
              <!-- rendered via JS badge list -->
            </div>
            <div class="input-group input-group-sm">
              <input type="url" id="managerSiteInput" class="form-control" placeholder="Add new URL (https://...)">
              <button class="btn btn-info text-white" type="button" onclick="managerAddSite()">Add</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Project Info Banner -->
  <div class="alert alert-success mb-4">
    <div class="row align-items-center">
      <div class="col-md-7">
        <strong><i class="fas fa-info-circle me-2"></i>Project:</strong>
        <span class="badge bg-dark me-2">Keyword: <?= clean($project['target_keyword']) ?></span>
        <span class="badge bg-dark me-2">Site: <?= clean($project['target_site'] ?: $project['website_url']) ?></span>
      </div>
      <div class="col-md-5 text-end">
        <a href="export-excel.php?id=<?= $selectedProjectId ?>" class="btn btn-success btn-sm me-2">
          <i class="fas fa-file-excel me-1"></i>Download Excel
        </a>
        <button class="btn btn-primary btn-sm" onclick="runAllSubmissions()">
          <i class="fas fa-rocket me-1"></i>Run All
        </button>
        <button class="btn btn-info btn-sm" onclick="bulkBlueskyPost(<?= $selectedProjectId ?>)">
          <i class="fas fa-paper-plane me-1"></i>Bulk Post All Accounts
        </button>
        <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#bulkAddPanel">
          <i class="fas fa-users-cog me-1"></i>Bulk Add Accounts
        </button>

        <!-- Universal Bulk Add Accounts Panel -->
        <div class="collapse mt-3" id="bulkAddPanel">
          <div class="card border-info shadow-sm">
            <div class="card-header bg-info text-white">
              <h6 class="mb-0"><i class="fas fa-users me-2"></i>Bulk Add Multiple Accounts — Any Platform</h6>
            </div>
            <div class="card-body">
              <p class="small text-muted mb-2">
                Format: <code>platform,username,password_or_apikey,api_secret(optional)</code><br>
                One account per line. Example:
              </p>
              <pre class="bg-dark text-success p-2 rounded small">bluesky,lmt20.bsky.social,ug4x-go7k-mdm3-5zld
bluesky,learnmore59.bsky.social,xgfn-obvs-wuvc-grac
devto,myuser2,api_key_here
plurk,myuser3,api_key_here
penzu,email@test.com,password123
github,myuser4,ghp_token_here
wordpress,myblog.wordpress.com,oauth_token_here</pre>
              <form method="post" action="submission-manager.php?project_id=<?= $selectedProjectId ?>">
                <input type="hidden" name="add_bulk_universal" value="1">
                <input type="hidden" name="project_id" value="<?= $selectedProjectId ?>">
                <textarea name="bulk_accounts" rows="8" class="form-control mb-2"
                  placeholder="bluesky,handle1.bsky.social,xxxx-xxxx-xxxx-xxxx&#10;bluesky,handle2.bsky.social,yyyy-yyyy-yyyy-yyyy&#10;devto,username,api_key_here"></textarea>
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-plus me-2"></i>Add All Accounts
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Logo Upload + Image Section -->
  <?php
  $postImage = $project['post_image'] ?? null;
  $imageUrl  = $postImage ? SITE_URL . '/uploads/' . $postImage : null;
  // Check if logo exists
  $logoExt  = @file_get_contents(__DIR__ . '/assets/logo_ext.txt');
  $logoPath = $logoExt ? __DIR__ . '/assets/logo.' . trim($logoExt) : null;
  $logoUrl  = ($logoPath && file_exists($logoPath)) ? SITE_URL . '/assets/logo.' . trim($logoExt) : null;
  ?>

  <!-- Logo Upload -->
  <div class="card mb-3 border-danger shadow-sm">
    <div class="card-header bg-danger text-white">
      <h6 class="mb-0"><i class="fas fa-trademark me-2"></i>Company Logo — Used in ALL generated images</h6>
    </div>
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-3 text-center">
          <?php if ($logoUrl): ?>
            <img src="<?= $logoUrl ?>?t=<?= time() ?>" style="max-height:80px;max-width:180px;" alt="Logo">
            <p class="small text-success mt-1"><i class="fas fa-check-circle"></i> Logo ready</p>
          <?php else: ?>
            <div class="border rounded p-3 text-muted text-center">
              <i class="fas fa-trademark fa-2x mb-1"></i><br>
              <small>No logo uploaded</small>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-md-9">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_logo" value="1">
            <input type="hidden" name="project_id" value="<?= $selectedProjectId ?>">
            <label class="form-label fw-bold">Upload Learnmore Technologies Logo (PNG recommended)</label>
            <div class="input-group">
              <input type="file" name="logo_image" class="form-control" accept="image/*" required>
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-upload me-1"></i>Upload Logo
              </button>
            </div>
            <small class="text-muted">PNG with transparent background works best</small>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="card mb-4 border-warning shadow-sm">
    <div class="card-header bg-warning text-dark">
      <h6 class="mb-0"><i class="fas fa-image me-2"></i>Post Image — System will use this image for ALL auto posts</h6>
    </div>
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-3 text-center">
          <?php if ($imageUrl): ?>
            <img src="<?= $imageUrl ?>" class="img-thumbnail" style="max-height:120px;" id="previewImg" alt="Post Image">
            <p class="small text-success mt-1"><i class="fas fa-check-circle"></i> Image ready</p>
            <button class="btn btn-sm btn-warning mt-1 w-100" onclick="addLogoToImage(<?= $selectedProjectId ?>, this)">
              <i class="fas fa-trademark me-1"></i>Add Logo to Image
            </button>
          <?php else: ?>
            <div class="border rounded p-3 text-muted text-center">
              <i class="fas fa-image fa-3x mb-2"></i><br>
              <small>No image uploaded</small>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-md-9">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_image" value="1">
            <input type="hidden" name="project_id" value="<?= $selectedProjectId ?>">
            <label class="form-label fw-bold">Upload your own image OR auto-generate below</label>
            <div class="input-group mb-2">
              <input type="file" name="post_image" class="form-control" accept="image/*">
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-upload me-1"></i>Upload
              </button>
            </div>
          </form>
          <div class="mt-2">
            <button class="btn btn-primary w-100" onclick="generateImage(<?= $selectedProjectId ?>, this)">
              <i class="fas fa-magic me-2"></i>Create Poster (Keyword + Phone + Email)
            </button>
            <small class="text-muted d-block mt-1">
              Generates unique marketing image with: <strong><?= clean($project['target_keyword']) ?></strong> + 9036354554 + office.learnmore@gmail.com + LT Logo
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Progress -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <div class="row text-center">
        <?php
        $totalSites = 0;
        foreach ($platforms as $cat) $totalSites += count($cat['sites']);
        $savedCount = count($savedMap);
        $submittedCount = $db->prepare("SELECT COUNT(*) FROM backlinks WHERE project_id=? AND status='created'");
        $submittedCount->execute([$selectedProjectId]);
        $submittedCount = $submittedCount->fetchColumn();
        ?>
        <div class="col-3">
          <h3 class="text-primary"><?= $totalSites ?></h3>
          <small>Total Sites</small>
        </div>
        <div class="col-3">
          <h3 class="text-warning"><?= $savedCount ?></h3>
          <small>Credentials Saved</small>
        </div>
        <div class="col-3">
          <h3 class="text-success"><?= $submittedCount ?></h3>
          <small>Posts Created</small>
        </div>
        <div class="col-3">
          <h3 class="text-info"><?= $totalSites - $submittedCount ?></h3>
          <small>Remaining</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Created Backlinks Table -->
  <?php
  $createdBL = $db->prepare("SELECT * FROM backlinks WHERE project_id=? AND status='created' ORDER BY created_at DESC");
  $createdBL->execute([$selectedProjectId]);
  $createdBL = $createdBL->fetchAll();
  ?>
  <?php if (!empty($createdBL)): ?>
  <div class="card mb-4 border-success shadow-sm">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-check-circle me-2"></i>
        Created Backlinks (<?= count($createdBL) ?>) — New URLs
      </h5>
      <a href="export-excel.php?id=<?= $selectedProjectId ?>" class="btn btn-light btn-sm">
        <i class="fas fa-file-excel me-1 text-success"></i>Download Excel
      </a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Platform</th>
              <th>Backlink URL</th>
              <th>Date Created</th>
              <th>Link Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($createdBL as $i => $bl): ?>
            <tr id="bl-row-<?= $bl['id'] ?>">
              <td><?= $i + 1 ?></td>
              <td>
                <span class="badge bg-success"><?= clean($bl['platform']) ?></span>
              </td>
              <td>
                <a href="<?= clean($bl['backlink_url']) ?>" target="_blank" class="text-decoration-none">
                  <?= clean(substr($bl['backlink_url'], 0, 60)) ?>...
                  <i class="fas fa-external-link-alt ms-1 small"></i>
                </a>
              </td>
              <td><small><?= date('d M Y H:i', strtotime($bl['created_at'])) ?></small></td>
              <td class="bl-status-cell">
                <?php
                $vStatus = $bl['verified_status'] ?? 'unverified';
                if ($vStatus === 'active') {
                    echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active (Dofollow)</span>';
                } elseif ($vStatus === 'nofollow') {
                    echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Nofollow</span>';
                } elseif ($vStatus === 'broken') {
                    echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Broken / Offline</span>';
                } else {
                    echo '<span class="badge bg-secondary"><i class="fas fa-question-circle me-1"></i>Unverified</span>';
                }
                if (!empty($bl['last_checked_at'])) {
                    echo '<br><small class="text-muted" style="font-size: 10px;">Checked: ' . date('d M, H:i', strtotime($bl['last_checked_at'])) . '</small>';
                }
                ?>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary"
                          onclick="navigator.clipboard.writeText('<?= clean($bl['backlink_url']) ?>').then(()=>alert('URL Copied!'))" title="Copy URL">
                    <i class="fas fa-copy"></i>
                  </button>
                  <button class="btn btn-outline-primary"
                          onclick="verifySingleLink(<?= $bl['id'] ?>, this)" title="Verify Live Link Now">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Platform Categories -->
  <?php foreach ($platforms as $catKey => $category): ?>
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-<?= $category['color'] ?> text-white">
      <h5 class="mb-0"><?= $category['title'] ?></h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Platform</th>
              <th>What System Does Automatically</th>
              <th>Your Credentials</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($category['sites'] as $site): ?>
            <?php $saved = $savedMap[$site['id']] ?? null;
                  $allAccounts = $savedMapAll[$site['id']] ?? []; ?>
            <tr>
              <td>
                <strong><?= $site['name'] ?></strong><br>
                <a href="<?= $site['url'] ?>" target="_blank" class="small text-muted">
                  <?= $site['url'] ?> <i class="fas fa-external-link-alt"></i>
                </a>
              </td>
              <td>
                <small class="text-success">
                  <i class="fas fa-robot me-1"></i><?= $site['what_system_does'] ?>
                </small>
              </td>
              <td>
                <?php if (!empty($allAccounts)): ?>
                  <?php foreach ($allAccounts as $acc): ?>
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <span class="text-success small">
                      <i class="fas fa-check-circle me-1"></i><?php
                        // For Mastodon: show email (api_secret) instead of instance
                        if ($site['id'] === 'mastodon' && !empty($acc['api_secret'])) {
                            echo clean($acc['api_secret']);
                        } else {
                            echo clean($acc['username']);
                        }
                      ?>
                    </span>
                    <button class="btn btn-xs btn-outline-danger py-0 px-1"
                            onclick="deleteAccount(<?= $acc['id'] ?>, this)"
                            title="Remove">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <?php endforeach; ?>
                  <?php if ($site['id'] === 'wordpress'): ?>
                    <button onclick="showWpConnect()" class="btn btn-xs btn-primary mt-1">
                      <i class="fab fa-wordpress me-1"></i>Re-Connect WordPress
                    </button>
                  <?php else: ?>
                  <button class="btn btn-xs btn-outline-primary mt-1"
                          onclick="showCredForm('<?= $site['id'] ?>', '<?= $site['name'] ?>', <?= $selectedProjectId ?>)">
                    <i class="fas fa-plus me-1"></i>Add More Account
                  </button>
                  <?php endif; ?>
                <?php else: ?>
                  <?php if ($site['id'] === 'wordpress'): ?>
                    <button onclick="showWpConnect()" class="btn btn-sm btn-primary">
                      <i class="fab fa-wordpress me-1"></i>Connect WordPress
                    </button>
                    <br><small class="text-muted">Email + Password → Auto-save</small>
                  <?php else: ?>
                  <button class="btn btn-sm btn-outline-primary"
                          onclick="showCredForm('<?= $site['id'] ?>', '<?= $site['name'] ?>', <?= $selectedProjectId ?>)">
                    <i class="fas fa-key me-1"></i>Add Credentials
                  </button>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td>
                <?php
                // Check if a backlink exists specifically for the selected keyword
                $blCheck = $db->prepare("SELECT id FROM backlinks WHERE project_id=? AND platform=? AND status='created' AND (keyword=? OR (keyword IS NULL AND (post_title LIKE ? OR backlink_url LIKE ?)))");
                $blCheck->execute([$selectedProjectId, $site['id'], $currentKeyword, '%' . $currentKeyword . '%', '%' . $currentKeyword . '%']);
                $posted = $blCheck->fetch();
                ?>
                <?php if ($posted): ?>
                  <span class="badge bg-success">✅ Posted</span>
                <?php elseif ($saved): ?>
                  <span class="badge bg-warning">Ready to Post</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Needs Credentials</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($allAccounts) && !$posted): ?>
                  <button class="btn btn-sm btn-success"
                          onclick="autoPostAll('<?= $site['id'] ?>', '<?= $site['name'] ?>', <?= $selectedProjectId ?>, <?= count($allAccounts) ?>)">
                    <i class="fas fa-paper-plane me-1"></i>Auto Post
                    <?php if (count($allAccounts) > 1): ?>
                      <span class="badge bg-warning text-dark ms-1"><?= count($allAccounts) ?> accounts</span>
                    <?php endif; ?>
                  </button>
                <?php elseif ($posted): ?>
                  <span class="text-success"><i class="fas fa-check"></i> Done</span>
                <?php else: ?>
                  <span class="text-muted small">Add credentials first</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>
</div>

<!-- Credentials Modal -->
<div class="modal fade" id="credModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-key me-2"></i>Add Credentials: <span id="modalPlatformName"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info small">
          <i class="fas fa-robot me-2"></i>
          <strong>System will automatically:</strong><br>
          <span id="modalWhatSystemDoes"></span>
        </div>
        <form method="POST" id="credForm">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="save_platform" value="1">
          <input type="hidden" name="platform" id="modalPlatformId">
          <input type="hidden" name="project_id" id="modalProjectId">

          <div class="mb-3">
            <label class="form-label fw-bold">Username / Email</label>
            <input type="text" name="username" class="form-control"
                   placeholder="Your username or email" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" id="modalPasswordLabel">Password</label>
            <input type="password" name="password" id="modalPasswordInput" class="form-control"
                   placeholder="Your password">
          </div>

          <!-- API Key section - shown for platforms that need it -->
          <div id="apiKeySection" class="mb-3">
            <label class="form-label fw-bold">
              API Key / Integration Token
              <span class="badge bg-danger ms-1">Required for Auto Post</span>
            </label>
            <input type="text" name="api_key" id="modalApiKey" class="form-control"
                   placeholder="Paste API key / token here">
            <div class="form-text" id="apiKeyHelp"></div>
          </div>

          <!-- No API notice - shown for email+password platforms -->
          <div id="noApiNotice" class="alert alert-success small" style="display:none;">
            <i class="fas fa-robot me-2"></i>
            <strong>Browser Automation Active</strong> — No API key needed!<br>
            <span id="noApiNoticeText">System will use Email + Password to auto-login and post.</span>
          </div>

          <div class="mb-3" id="apiSecretSection" style="display:none;">
            <label class="form-label fw-bold">API Secret / Blog Name</label>
            <input type="text" name="api_secret" class="form-control"
                   placeholder="API secret or blog name (e.g. yourblog.tumblr.com)">
          </div>

          <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="fas fa-save me-2"></i>Save Credentials
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Auto Post Progress Modal -->
<div class="modal fade" id="postModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-rocket me-2"></i>Auto Posting...</h5>
      </div>
      <div class="modal-body text-center py-4">
        <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
        <h5 id="postingStatus">System is posting...</h5>
        <p class="text-muted" id="postingDetail">Please wait...</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- WordPress Connect Modal -->
<div class="modal fade" id="wpConnectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fab fa-wordpress me-2"></i>Connect WordPress.com</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="wpConnectForm">
          <p class="text-muted small mb-3">Email + Password enter કરો — token auto-fetch થઈ system માં save થશે</p>
          <div class="mb-3">
            <label class="form-label fw-bold">WordPress.com Email</label>
            <input type="email" id="wpEmail" class="form-control" value="kanzariyapratik124@gmail.com" placeholder="email@gmail.com">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" id="wpPassword" class="form-control" placeholder="WordPress.com password">
          </div>
          <div id="wpConnectMsg" class="mb-2"></div>
        </div>
        <div id="wpConnectSuccess" class="d-none text-center py-3">
          <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
          <h5 class="text-success">Connected Successfully!</h5>
          <p id="wpSiteLabel" class="text-muted"></p>
          <a id="wpPostLink" href="#" target="_blank" class="btn btn-success mt-2">
            <i class="fas fa-external-link-alt me-1"></i>View Test Post
          </a>
        </div>
      </div>
      <div class="modal-footer" id="wpConnectFooter">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="wpConnectBtn" onclick="doWpConnect()">
          <i class="fab fa-wordpress me-1"></i>Connect & Auto-Post
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const PROJECT_ID = <?= $selectedProjectId ?>;

const siteInfo = {
<?php foreach ($platforms as $cat): foreach ($cat['sites'] as $site): ?>
  '<?= $site['id'] ?>': { name: '<?= $site['name'] ?>', does: '<?= addslashes($site['what_system_does']) ?>' },
<?php endforeach; endforeach; ?>
};

function showWpConnect() {
  document.getElementById('wpConnectForm').classList.remove('d-none');
  document.getElementById('wpConnectSuccess').classList.add('d-none');
  document.getElementById('wpConnectFooter').classList.remove('d-none');
  document.getElementById('wpConnectMsg').innerHTML = '';
  new bootstrap.Modal(document.getElementById('wpConnectModal')).show();
}

function doWpConnect() {
  const email    = document.getElementById('wpEmail').value.trim();
  const password = document.getElementById('wpPassword').value;
  const btn      = document.getElementById('wpConnectBtn');
  const msg      = document.getElementById('wpConnectMsg');

  if (!email || !password) {
    msg.innerHTML = '<div class="alert alert-warning py-2">Email અને Password enter કરો</div>';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Connecting...';
  msg.innerHTML = '';

  const fd = new FormData();
  fd.append('wp_ajax_connect', '1');
  fd.append('wp_email', email);
  fd.append('wp_password', password);
  fd.append('project_id', PROJECT_ID);
  fd.append('csrf_token', '<?= csrfToken() ?>');

  fetch('submission-manager.php', {
    method: 'POST',
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('wpConnectForm').classList.add('d-none');
      document.getElementById('wpConnectFooter').classList.add('d-none');
      document.getElementById('wpSiteLabel').textContent = 'Site: ' + data.site;
      if (data.post_url) {
        document.getElementById('wpPostLink').href = data.post_url;
        document.getElementById('wpPostLink').classList.remove('d-none');
      }
      document.getElementById('wpConnectSuccess').classList.remove('d-none');
      setTimeout(() => location.reload(), 3000);
    } else {
      msg.innerHTML = '<div class="alert alert-danger py-2">❌ ' + (data.error || 'Failed') + '</div>';
      btn.disabled = false;
      btn.innerHTML = '<i class="fab fa-wordpress me-1"></i>Connect & Auto-Post';
    }
  })
  .catch(err => {
    msg.innerHTML = '<div class="alert alert-danger py-2">Error: ' + err.message + '</div>';
    btn.disabled = false;
    btn.innerHTML = '<i class="fab fa-wordpress me-1"></i>Connect & Auto-Post';
  });
}

function showCredForm(platformId, platformName, projectId) {
  document.getElementById('modalPlatformId').value = platformId;
  document.getElementById('modalProjectId').value = projectId;
  document.getElementById('modalPlatformName').textContent = platformName;
  document.getElementById('modalWhatSystemDoes').textContent = siteInfo[platformId]?.does || '';

  // Dynamically change password label & placeholder for Bluesky
  const passwordLabel = document.getElementById('modalPasswordLabel');
  const passwordInput = document.getElementById('modalPasswordInput');
  const usernameLabel = document.querySelector('label[for="modalUsername"], .modal-body label:first-of-type');
  const usernameInput = document.querySelector('input[name="username"]');

  if (platformId === 'bluesky') {
    passwordLabel.textContent = 'App Password';
    passwordInput.placeholder = 'Your Bluesky App Password (e.g. xxxx-xxxx-xxxx-xxxx)';
    if (usernameInput) usernameInput.placeholder = 'Your Bluesky handle (e.g. user.bsky.social)';
  } else if (platformId === 'mastodon') {
    passwordLabel.textContent = 'Password';
    passwordInput.placeholder = 'Your Mastodon password';
    // For mastodon, username = email address
    if (usernameInput) {
      usernameInput.placeholder = 'Your Mastodon email (e.g. you@gmail.com)';
      usernameInput.type = 'email';
    }
  } else {
    passwordLabel.textContent = 'Password';
    passwordInput.placeholder = 'Your password';
    if (usernameInput) {
      usernameInput.placeholder = 'Your username or email';
      usernameInput.type = 'text';
    }
  }

  // Show API key help based on platform
  const apiKeyHelp = {
    'pinterest':  '🤖 Browser Automation — No API key needed! Enter Pinterest Email + Password. System will open Chrome, auto-login, and create a pin with image + keyword + backlink automatically.',
    'mastodon':   '🤖 Browser Automation — No API key needed! Enter Mastodon Email + Password. System will auto-login, create an app, generate access token, and post automatically!',
    'wordpress': 'Get token: <a href="https://developer.wordpress.com/apps/" target="_blank">developer.wordpress.com/apps</a> → Create New App → Access Token',
    'tumblr':    'Get key: <a href="https://www.tumblr.com/oauth/apps" target="_blank">tumblr.com/oauth/apps</a> → Register App → OAuth Consumer Key',
    'blogger':   'Get token: <a href="https://developers.google.com/oauthplayground" target="_blank">OAuth Playground</a> → Blogger scope → Access Token. Blog ID in API Secret.',
    'github':    'Get token: <a href="https://github.com/settings/tokens" target="_blank">github.com/settings/tokens</a> → Generate token → repo scope',
    'devto':     'Get key: <a href="https://dev.to/settings/extensions" target="_blank">dev.to/settings/extensions</a> → DEV Community API Keys → Generate',
    'hashnode':  'Get key: <a href="https://hashnode.com/settings/developer" target="_blank">hashnode.com/settings/developer</a> → Personal Access Token. Publication ID in API Secret.',
    'ghost':     'Ghost Admin → Settings → Integrations → Add Custom Integration → Admin API Key. Ghost site URL in Username.',
    'bluesky':   'No API key needed! Enter your Bluesky username + App Password (not your regular login password). Get App Password: <a href="https://bsky.app/settings/app-passwords" target="_blank">bsky.app → Settings → App Passwords</a>',
    'minds':     'Get token: <a href="https://www.minds.com/settings/security" target="_blank">minds.com → Settings → Security → API Token</a>',
    'plurk':     'Get key: <a href="https://www.plurk.com/API/Apps" target="_blank">plurk.com/API/Apps</a> → Create App → API Key',
    'mediafire': '✅ No API key needed! Enter your MediaFire Email + Password. System will log in, generate a PDF with keyword description + backlink, and upload it automatically.',
    'fourshared': '✅ No API key needed! Enter your 4Shared Email + Password. System will log in, generate a PDF with keyword description + backlink, and upload it automatically.',
  };

  // Platforms that need NO API key — ChatGPT content, copy-paste manually
  const noApiNeeded = ['pinterest','mastodon','minds','symbaloo','penzu','linktree',
    'scoopit','wakelet','vivauae','padlet','pearltrees','mewe','instapaper',
    'gifyu','postimage','photobucket','behance','pbase','dropbox','imgbb',
    'site123','posteezy','livejournal','justpaste','substack','dribbble',
    'limewire','workupload','powershow','uploadee','pdfhost',
    'mediafire','fourshared'];

  const helpText = apiKeyHelp[platformId] || (noApiNeeded.includes(platformId)
    ? '✅ No API key needed! Save Email + Password. System auto-login kare + post kare.'
    : 'API key required for auto posting');
  document.getElementById('apiKeyHelp').innerHTML = helpText;

  // Hide API key section for no-API platforms + Bluesky
  const apiSection = document.getElementById('apiKeySection');
  const apiSecretSection = document.getElementById('apiSecretSection');
  const noApiNotice = document.getElementById('noApiNotice');
  if (noApiNeeded.includes(platformId) || platformId === 'bluesky') {
    apiSection.style.display = 'none';
    if(apiSecretSection) apiSecretSection.style.display = 'none';
    if(noApiNotice) {
      noApiNotice.style.display = '';
      // Platform-specific notice text
      const noticeText = document.getElementById('noApiNoticeText');
      if(noticeText) {
        if(platformId === 'pinterest') {
          noticeText.innerHTML = '🤖 <strong>Selenium Browser Automation</strong> — System will open Chrome headless, login to Pinterest with your credentials, upload image + create pin automatically!';
        } else if(platformId === 'mastodon') {
          noticeText.innerHTML = '🤖 <strong>Selenium Browser Automation</strong> — System will login to Mastodon, create developer app, generate access token automatically, and post! Token will be saved for next time.';
        } else if(['mediafire','fourshared'].includes(platformId)) {
          noticeText.innerHTML = '✅ System logs in with Email + Password → generates PDF → uploads automatically.';
        } else {
          noticeText.innerHTML = 'System will use Email + Password to auto-login and post.';
        }
      }
    }
  } else {
    apiSection.style.display = '';
    if(noApiNotice) noApiNotice.style.display = 'none';
    if(apiSecretSection) apiSecretSection.style.display = ['tumblr','wordpress','blogger','hashnode'].includes(platformId) ? '' : 'none';
  }

  new bootstrap.Modal(document.getElementById('credModal')).show();
}

function updateAutoPostSelection() {
  const kw = document.getElementById('backlinkKeywordSelect').value;
  const site = document.getElementById('backlinkUrlSelect').value;
  location.href = 'submission-manager.php?project_id=' + PROJECT_ID + '&keyword=' + encodeURIComponent(kw) + '&target_site=' + encodeURIComponent(site);
}

function autoPost(platformId, platformName, projectId) {
  autoPostAll(platformId, platformName, projectId);
}

function autoPostAll(platformId, platformName, projectId) {
  const modal = new bootstrap.Modal(document.getElementById('postModal'));
  document.getElementById('postingStatus').textContent = 'Posting to ' + platformName + '...';
  document.getElementById('postingDetail').textContent = 'Posting to all accounts simultaneously...';
  modal.show();

  const kwSelect = document.getElementById('backlinkKeywordSelect');
  const kw = kwSelect ? encodeURIComponent(kwSelect.value) : '';
  const siteSelect = document.getElementById('backlinkUrlSelect');
  const siteUrl = siteSelect ? encodeURIComponent(siteSelect.value) : '';
  fetch('auto-poster.php?id=' + projectId + '&platform=' + platformId + '&all_accounts=1&keyword=' + kw + '&target_site=' + siteUrl, {
    signal: AbortSignal.timeout(300000),
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
    .then(r => r.text())
    .then(text => {
      try { return JSON.parse(text); }
      catch (err) {
        const preview = text.replace(/<[^>]*>/g, ' ').trim().slice(0, 300);
        throw new Error('Server error: ' + preview);
      }
    })
    .then(data => {
      if (data.success) {
        document.getElementById('postingStatus').innerHTML = '✅ Posted Successfully!';
        const countMsg = data.posted_count > 1 ? `<strong>${data.posted_count} accounts</strong> posted!<br>` : '';
        document.getElementById('postingDetail').innerHTML =
          countMsg + 'Backlink: <a href="' + data.url + '" target="_blank">' + data.url + '</a>';
        setTimeout(() => { modal.hide(); location.reload(); }, 3000);
      } else if (data.manual) {
        modal.hide();
        showManualContent(platformName, data.content, data.title, data.url || siteInfo[platformId]?.url, data.pdf_url || null);
      } else {
        document.getElementById('postingStatus').innerHTML = '⚠️ ' + (data.error || 'Failed');
        document.getElementById('postingDetail').innerHTML =
          'Check credentials. <a href="' + (siteInfo[platformId]?.url || '#') + '" target="_blank">Open site</a>';
      }
    })
    .catch((err) => {
      document.getElementById('postingStatus').textContent = '⚠️ ' + (err.message || 'Error');
      document.getElementById('postingDetail').textContent = 'Please try again.';
    });
}

function deleteAccount(accountId, btn) {
  if (!confirm('Remove this account?')) return;
  fetch('submission-manager.php?delete_account=' + accountId + '&csrf=<?= csrfToken() ?>', {
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      btn.closest('.d-flex').remove();
    } else {
      alert('Error: ' + (d.error || 'Could not delete'));
    }
  });
}

function bulkBlueskyPost(projectId) {
  const modal = new bootstrap.Modal(document.getElementById('postModal'));
  document.getElementById('postingStatus').textContent = '🚀 Bulk posting to ALL Bluesky accounts...';
  document.getElementById('postingDetail').innerHTML =
    '<div class="spinner-border spinner-border-sm me-2"></div>Posting in parallel batches of 10... Please wait (may take 2-3 minutes for 50 accounts)';
  modal.show();

  const kwSelect = document.getElementById('backlinkKeywordSelect');
  const kw = kwSelect ? encodeURIComponent(kwSelect.value) : '';
  const siteSelect = document.getElementById('backlinkUrlSelect');
  const siteUrl = siteSelect ? encodeURIComponent(siteSelect.value) : '';
  fetch('auto-post-all.php?id=' + projectId + '&platform=bluesky&keyword=' + kw + '&target_site=' + siteUrl, {
    signal: AbortSignal.timeout(600000), // 10 minute timeout for 50 accounts
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(r => r.text())
  .then(text => {
    try { return JSON.parse(text); }
    catch(e) { throw new Error('Server error: ' + text.replace(/<[^>]*>/g,' ').trim().slice(0,300)); }
  })
  .then(data => {
    const success = data.posted  || 0;
    const failed  = data.failed  || 0;
    const skipped = data.skipped || 0;
    document.getElementById('postingStatus').innerHTML =
      `✅ Bulk Complete! <span class="badge bg-success">${success} posted</span> ` +
      `<span class="badge bg-warning text-dark">${skipped} skipped</span> ` +
      `<span class="badge bg-danger">${failed} failed</span>`;

    // Build results table
    let table = '<div style="max-height:200px;overflow-y:auto"><table class="table table-sm table-bordered mt-2 mb-0"><thead><tr><th>Account</th><th>Status</th><th>URL</th></tr></thead><tbody>';
    (data.results || []).forEach(r => {
      const badge = r.status === 'success' ? 'bg-success' : r.status === 'skipped' ? 'bg-warning text-dark' : 'bg-danger';
      const link  = r.url ? `<a href="${r.url}" target="_blank">View</a>` : (r.message || '-');
      table += `<tr><td class="small">${r.handle||r.platform}</td><td><span class="badge ${badge}">${r.status}</span></td><td class="small">${link}</td></tr>`;
    });
    table += '</tbody></table></div>';
    document.getElementById('postingDetail').innerHTML = table;
    setTimeout(() => { modal.hide(); location.reload(); }, 6000);
  })
  .catch(err => {
    document.getElementById('postingStatus').textContent = '⚠️ Error: ' + (err.message || 'Connection failed');
    document.getElementById('postingDetail').textContent = 'Bulk posting failed. Check server logs.';
  });
}


function showManualContent(platformName, content, title, siteUrl, pdfUrl = null) {
  // Create a modal to show generated content for copy-paste
  const existing = document.getElementById('manualModal');
  if (existing) existing.remove();

  const pdfBtn = pdfUrl
    ? `<a href="${pdfUrl}" download class="btn btn-danger btn-lg w-100 mb-2">
         <i class="fas fa-file-pdf me-2"></i>Download Generated PDF
       </a>`
    : '';

  const html = `
  <div class="modal fade" id="manualModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">
            <i class="fas fa-robot me-2"></i>ChatGPT Content Ready — Post on ${platformName}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          ${pdfUrl ? `
          <div class="alert alert-success">
            <strong><i class="fas fa-file-pdf me-2"></i>PDF Generated!</strong>
            Steps: 1. Download PDF below → 2. <a href="${siteUrl}" target="_blank">Open ${platformName}</a> →
            3. Upload the PDF → 4. Copy share link → 5. Click <strong>Mark as Created</strong>
          </div>
          ${pdfBtn}
          ` : `
          <div class="alert alert-info">
            <strong>Steps:</strong>
            1. Copy the title and content below →
            2. <a href="${siteUrl}" target="_blank">Open ${platformName}</a> →
            3. Create new post → Paste → Publish
          </div>
          `}
          ${!pdfUrl ? `
          <div class="mb-3">
            <label class="fw-bold">Title:</label>
            <div class="input-group">
              <input type="text" class="form-control" id="manualTitle" value="${(title||'').replace(/"/g,'&quot;')}" readonly>
              <button class="btn btn-outline-secondary" onclick="copyField('manualTitle')">
                <i class="fas fa-copy"></i> Copy
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label class="fw-bold">Content (HTML):</label>
            <div class="d-flex justify-content-end mb-1">
              <button class="btn btn-sm btn-primary" onclick="copyField('manualContent')">
                <i class="fas fa-copy me-1"></i>Copy All Content
              </button>
            </div>
            <textarea class="form-control" id="manualContent" rows="12" readonly>${content||''}</textarea>
          </div>
          ` : `<div class="mt-2 p-3 bg-light rounded small">${content||''}</div>`}
          <a href="${siteUrl}" target="_blank" class="btn btn-success btn-lg w-100 mt-2">
            <i class="fas fa-external-link-alt me-2"></i>Open ${platformName}
          </a>
        </div>
      </div>
    </div>
  </div>`;

  document.body.insertAdjacentHTML('beforeend', html);
  new bootstrap.Modal(document.getElementById('manualModal')).show();
}

function copyField(id) {
  const el = document.getElementById(id);
  el.select();
  navigator.clipboard.writeText(el.value).then(() => {
    showToast('Copied to clipboard!', 'success');
  });
}

function addLogoToImage(projectId, btn) {
  if (!btn) btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

  fetch('add-logo.php?project_id=' + projectId, {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Update preview image
        const img = document.getElementById('previewImg');
        if (img) img.src = data.image;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Logo Added!';
        btn.classList.replace('btn-warning', 'btn-success');
        setTimeout(() => {
          btn.innerHTML = '<i class="fas fa-trademark me-1"></i>Add Logo Again';
          btn.classList.replace('btn-success', 'btn-warning');
          btn.disabled = false;
        }, 3000);
      } else {
        btn.innerHTML = '<i class="fas fa-trademark me-1"></i>Add Logo to Image';
        btn.disabled = false;
        alert('Error: ' + (data.error || 'Failed'));
      }
    })
    .catch(() => {
      btn.innerHTML = '<i class="fas fa-trademark me-1"></i>Add Logo to Image';
      btn.disabled = false;
      alert('Connection error. Try again.');
    });
}

function generateImage(projectId, btn) {
  if (!btn) btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>ChatGPT creating image...';

  fetch('image-generator.php?generate=1&project_id=' + projectId, {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Show generated image
        const imgDiv = btn.closest('.col-md-9').previousElementSibling;
        imgDiv.innerHTML = '<img src="' + data.image + '?t=' + Date.now() + '" class="img-thumbnail" style="max-height:120px;" alt="Generated"><p class="small text-success mt-1"><i class="fas fa-check-circle"></i> Image ready</p>';
        btn.innerHTML = '<i class="fas fa-sync me-2"></i>Regenerate Image';
        btn.disabled = false;
        alert('✅ Marketing poster ready (AWS-style)! Now click Auto Post.');
      } else {
        btn.innerHTML = '<i class="fas fa-magic me-2"></i>Auto Generate Image';
        btn.disabled = false;
        alert('Error: ' + (data.error || 'Failed'));
      }
    })
    .catch(() => {
      btn.innerHTML = '<i class="fas fa-magic me-2"></i>Auto Generate Image';
      btn.disabled = false;
      alert('Connection error. Try again.');
    });
}

function runAllSubmissions() {
  const readySites = document.querySelectorAll('.btn-success[onclick*="autoPost"]');
  if (readySites.length === 0) {
    alert('No sites ready. Please add credentials first.');
    return;
  }
  if (!confirm('System will auto-post to ' + readySites.length + ' sites. Continue?')) return;

  let i = 0;
  function postNext() {
    if (i >= readySites.length) {
      alert('All submissions complete!');
      location.reload();
      return;
    }
    readySites[i].click();
    i++;
    setTimeout(postNext, 4000);
  }
  postNext();
}

function verifySingleLink(blId, btn) {
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  
  const row = document.getElementById('bl-row-' + blId);
  const statusCell = row ? row.querySelector('.bl-status-cell') : null;
  
  fetch('submission-manager.php?verify_backlink_ajax=' + blId, {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      
      if (data.success) {
        if (statusCell) {
          let badge = '';
          if (data.status === 'active') {
            badge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active (Dofollow)</span>';
          } else if (data.status === 'nofollow') {
            badge = '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Nofollow</span>';
          } else if (data.status === 'broken') {
            badge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Broken / Offline</span>';
          } else {
            badge = '<span class="badge bg-secondary"><i class="fas fa-question-circle me-1"></i>Unverified</span>';
          }
          statusCell.innerHTML = badge + '<br><small class="text-muted" style="font-size: 10px;">Checked: ' + data.last_checked + '</small>';
        }
      } else {
        alert('Verification failed: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      alert('Network error verifying link: ' + err.message);
    });
}

// Manager Arrays
let managerKeywords = <?= json_encode(array_filter(array_map('trim', explode(',', $project['target_keyword'])))) ?>;
let managerSites = <?= json_encode(array_filter(array_map('trim', explode(',', $project['target_site'])))) ?>;

document.addEventListener("DOMContentLoaded", () => {
  renderManagerKeywords();
  renderManagerSites();

  // Bind Enter key inside target list manager inputs
  document.getElementById('managerKeywordInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      managerAddKeyword();
    }
  });
  document.getElementById('managerSiteInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      managerAddSite();
    }
  });
});

function renderManagerKeywords() {
  const container = document.getElementById('managerKeywordsContainer');
  if (!container) return;
  container.innerHTML = '';
  if (managerKeywords.length === 0) {
    container.innerHTML = '<span class="text-muted small">No keywords added yet.</span>';
  }
  managerKeywords.forEach(kw => {
    const badge = document.createElement('span');
    badge.className = 'badge bg-primary text-white d-inline-flex align-items-center gap-2 py-2 px-3 rounded-pill';
    badge.innerHTML = `<span>${escapeHTML(kw)}</span><button type="button" class="btn-close btn-close-white" style="font-size:0.65rem;" onclick="managerRemoveKeyword('${escapeHTML(kw)}')"></button>`;
    container.appendChild(badge);
  });
}

function renderManagerSites() {
  const container = document.getElementById('managerSitesContainer');
  if (!container) return;
  container.innerHTML = '';
  if (managerSites.length === 0) {
    container.innerHTML = '<span class="text-muted small">No target URLs added yet.</span>';
  }
  managerSites.forEach(site => {
    const badge = document.createElement('span');
    badge.className = 'badge bg-info text-white d-inline-flex align-items-center gap-2 py-2 px-3 rounded-pill';
    badge.innerHTML = `<span>${escapeHTML(site)}</span><button type="button" class="btn-close btn-close-white" style="font-size:0.65rem;" onclick="managerRemoveSite('${escapeHTML(site)}')"></button>`;
    container.appendChild(badge);
  });
}

function saveManagerChanges() {
  const fd = new FormData();
  fd.append('update_project_targets', '1');
  fd.append('project_id', PROJECT_ID);
  fd.append('target_keywords', managerKeywords.join(', '));
  fd.append('target_sites', managerSites.join(', '));
  fd.append('csrf_token', '<?= csrfToken() ?>');

  fetch('submission-manager.php', {
    method: 'POST',
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // Reload page with selected parameters preserved
      const currentKeyword = document.getElementById('backlinkKeywordSelect').value;
      const currentSite = document.getElementById('backlinkUrlSelect').value;
      location.href = 'submission-manager.php?project_id=' + PROJECT_ID + '&keyword=' + encodeURIComponent(currentKeyword) + '&target_site=' + encodeURIComponent(currentSite);
    } else {
      alert('Error updating lists: ' + data.error);
    }
  });
}

function managerAddKeyword() {
  const input = document.getElementById('managerKeywordInput');
  if (!input) return;
  const val = input.value.trim();
  if (val && !managerKeywords.includes(val)) {
    managerKeywords.push(val);
    input.value = '';
    renderManagerKeywords();
    saveManagerChanges();
  }
}

function managerRemoveKeyword(kw) {
  managerKeywords = managerKeywords.filter(k => k !== kw);
  renderManagerKeywords();
  saveManagerChanges();
}

function managerAddSite() {
  const input = document.getElementById('managerSiteInput');
  if (!input) return;
  const val = input.value.trim();
  if (val) {
    if (!val.startsWith('http://') && !val.startsWith('https://')) {
      alert('Please enter a valid URL starting with http:// or https://');
      return;
    }
    if (!managerSites.includes(val)) {
      managerSites.push(val);
      input.value = '';
      renderManagerSites();
      saveManagerChanges();
    }
  }
}

function managerRemoveSite(site) {
  managerSites = managerSites.filter(s => s !== site);
  renderManagerSites();
  saveManagerChanges();
}

function escapeHTML(str) {
  return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>
</body>
</html>
