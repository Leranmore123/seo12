<?php
// ============================================================
// config.php - SEO 80/20 System Configuration
// ============================================================

// Load local overrides (API keys, DB password) — copy config.local.php.example
$_seoLocal = [];
if (is_readable(__DIR__ . '/config.local.php')) {
    $_seoLocal = (array) include __DIR__ . '/config.local.php';
}

function seoCfg(string $key, $default = '') {
    global $_seoLocal;
    $env = getenv($key);
    if ($env !== false && $env !== '') {
        return $env;
    }
    return $_seoLocal[$key] ?? $default;
}

define('DB_HOST', (string) seoCfg('DB_HOST', '127.0.0.1:3306'));
define('DB_USER', (string) seoCfg('DB_USER', 'root'));
define('DB_PASS', (string) seoCfg('DB_PASS', ''));
define('DB_NAME', (string) seoCfg('DB_NAME', 'seo_system'));

define('SITE_NAME', 'SEO 80/20 System');

// Auto-detect site URL (fixes menu when folder is not /seo-system/)
function detectSiteUrl(): string {
    if (!empty($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir    = rtrim(dirname($script), '/\\');
        if ($dir === '/' || $dir === '.') {
            $dir = '';
        }
        return $scheme . '://' . $host . $dir;
    }
    return rtrim((string) seoCfg('SITE_URL', 'http://localhost/seo-system'), '/');
}

define('SITE_URL', rtrim((string) seoCfg('SITE_URL', detectSiteUrl()), '/'));

// API keys — set in config.local.php (see api-setup.php)
define('OPENAI_API_KEY',       (string) seoCfg('OPENAI_API_KEY', ''));
define('OPENAI_MODEL',         (string) seoCfg('OPENAI_MODEL', 'gpt-4o-mini'));
define('OPENAI_IMAGE_MODEL',   (string) seoCfg('OPENAI_IMAGE_MODEL', 'dall-e-3'));
define('OPENAI_IMAGE_SIZE',    (string) seoCfg('OPENAI_IMAGE_SIZE', '1024x1024'));
define('GEMINI_API_KEY',       (string) seoCfg('GEMINI_API_KEY', ''));
define('STABILITY_API_KEY',    (string) seoCfg('STABILITY_API_KEY', ''));
define('HUGGINGFACE_API_KEY',  (string) seoCfg('HUGGINGFACE_API_KEY', ''));
define('GOOGLE_API_KEY',       (string) seoCfg('GOOGLE_API_KEY', ''));
define('GOOGLE_CSE_CX',        (string) seoCfg('GOOGLE_CSE_CX', ''));
define('DATAFORSEO_LOGIN',     (string) seoCfg('DATAFORSEO_LOGIN', ''));
define('DATAFORSEO_PASSWORD',  (string) seoCfg('DATAFORSEO_PASSWORD', ''));

define('SMTP_HOST', (string) seoCfg('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USER', (string) seoCfg('SMTP_USER', ''));
define('SMTP_PASS', (string) seoCfg('SMTP_PASS', ''));
define('SMTP_PORT', (int) seoCfg('SMTP_PORT', 587));

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure upload folders exist
foreach (['uploads', 'assets', 'logs'] as $_dir) {
    $_path = __DIR__ . '/' . $_dir;
    if (!is_dir($_path)) {
        @mkdir($_path, 0755, true);
    }
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            if (!empty($_GET['ajax']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                die(json_encode(['error' => 'Database connection failed. Import database.sql in phpMyAdmin.']));
            }
            die('<div style="font-family:sans-serif;max-width:600px;margin:40px auto;padding:24px;border:1px solid #dc3545;border-radius:8px;">'
                . '<h3>Database connection failed</h3>'
                . '<p>' . htmlspecialchars($e->getMessage()) . '</p>'
                . '<ol><li>Start MySQL (XAMPP/WAMP)</li>'
                . '<li>Import <code>database.sql</code> in phpMyAdmin</li>'
                . '<li>Edit <code>config.php</code> — DB_HOST, DB_USER, DB_PASS</li></ol>'
                . '<p><a href="setup.php">Run setup check</a></p></div>');
        }
    }
    return $pdo;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function requireMenuPermission($menuCode) {
    requireLogin();
    // Admin role gets absolute access to all pages
    if (($_SESSION['role'] ?? 'client') === 'admin') {
        return;
    }
    $userId = $_SESSION['user_id'] ?? 0;
    if ($userId > 0) {
        $db = getDB();
        $stmt = $db->prepare("SELECT allowed_menus FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $val = $stmt->fetchColumn();
        if ($val !== null && $val !== '') {
            $allowed = array_map('trim', explode(',', strtolower($val)));
            if (!in_array(strtolower($menuCode), $allowed)) {
                setFlash('danger', 'Access denied. You do not have permission to access that page.');
                header('Location: ' . SITE_URL . '/dashboard.php');
                exit;
            }
        }
    }
}

function clean($input) {
    return htmlspecialchars(strip_tags(trim((string) $input)), ENT_QUOTES, 'UTF-8');
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function hasApiKey(string $name): bool {
    $v = constant($name);
    return is_string($v) && $v !== '' && strpos($v, 'your-') !== 0 && strpos($v, 'paste-') !== 0;
}

function hasChatGPT(): bool {
    $k = OPENAI_API_KEY;
    return $k !== '' && strpos($k, 'sk-') === 0;
}

function hasGemini(): bool {
    $k = GEMINI_API_KEY;
    return $k !== '' && strpos($k, 'your-') === false;
}

/**
 * Shared utility to check if a backlink webpage links back to client's website.
 */
function verifyBacklink(string $backlinkUrl, string $clientUrl, ?string $targetUrl): string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $backlinkUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $html     = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode < 200 || $httpCode >= 400 || empty($html)) {
        return 'broken';
    }
    
    $clientHost = parse_url($clientUrl, PHP_URL_HOST);
    $targetHost = !empty($targetUrl) ? parse_url($targetUrl, PHP_URL_HOST) : null;
    
    if (empty($clientHost)) {
        return 'broken';
    }
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    @$dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $anchors = $xpath->query('//a');
    
    $found      = false;
    $isNofollow = false;
    
    foreach ($anchors as $a) {
        $href = trim($a->getAttribute('href'));
        $rel  = strtolower(trim($a->getAttribute('rel')));
        
        $match = false;
        if (strpos($href, $clientHost) !== false) {
            $match = true;
        }
        if ($targetHost && strpos($href, $targetHost) !== false) {
            $match = true;
        }
        
        if ($match) {
            $found = true;
            if (strpos($rel, 'nofollow') !== false) {
                $isNofollow = true;
            }
        }
    }
    
    if ($found) {
        return $isNofollow ? 'nofollow' : 'active';
    }
    
    return 'broken';
}

/**
 * Pings Google, Bing, and Ping-o-Matic to crawl new backlinks instantly.
 */
function pingSearchEngines(string $backlinkUrl, ?string $title = null): void {
    if (empty($backlinkUrl) || filter_var($backlinkUrl, FILTER_VALIDATE_URL) === false) {
        return;
    }
    $title = $title ?: 'New Backlink Created';
    $encodedUrl = urlencode($backlinkUrl);
    
    // 1. Bing Sitemap Ping
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://www.bing.com/ping?sitemap=" . $encodedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    // 2. Google Sitemap Ping
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://www.google.com/ping?sitemap=" . $encodedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    // 3. Ping-O-Matic XML-RPC Ping (Notifies Bing, Google Blogs, Yahoo, etc.)
    $xml = "<?xml version=\"1.0\"?>
    <methodCall>
      <methodName>weblogUpdates.ping</methodName>
      <params>
        <param><value>" . htmlspecialchars($title) . "</value></param>
        <param><value>" . htmlspecialchars($backlinkUrl) . "</value></param>
      </params>
    </methodCall>";
    
    $ch = curl_init('http://rpc.pingomatic.com/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => ['Content-Type: text/xml'],
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function getOnboardingToken(int $projectId, string $websiteUrl): string {
    return md5($projectId . $websiteUrl . 'seo_onboarding_secure_salt_2026');
}

function checkGoogleRank($keyword, $targetSite) {
    $domain = parse_url($targetSite, PHP_URL_HOST) ?: $targetSite;
    $domain = str_replace('www.', '', strtolower($domain));

    // ── Method 0: Google Custom Search Engine (CSE) API (Official & 100% Free 100/day) ──
    $googleApiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';
    $googleCseCx  = defined('GOOGLE_CSE_CX') ? GOOGLE_CSE_CX : '';
    if (!empty($googleApiKey) && !empty($googleCseCx)) {
        $cseUrl = "https://www.googleapis.com/customsearch/v1?key=" . urlencode($googleApiKey) . "&cx=" . urlencode($googleCseCx) . "&q=" . urlencode($keyword) . "&num=10";
        $ch = curl_init($cseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($resp, true);
        $items = $data['items'] ?? [];
        foreach ($items as $pos => $item) {
            $itemUrl = $item['link'] ?? '';
            $ld = str_replace('www.', '', strtolower(parse_url($itemUrl, PHP_URL_HOST) ?: ''));
            if (stripos($ld, $domain) !== false) {
                return $pos + 1;
            }
        }
    }

    // ── Method 1: DataForSEO SERP API (most accurate) ────────
    $login    = defined('DATAFORSEO_LOGIN')    ? DATAFORSEO_LOGIN    : '';
    $password = defined('DATAFORSEO_PASSWORD') ? DATAFORSEO_PASSWORD : '';

    if ($login && $password) {
        $postData = json_encode([[
            'keyword'       => $keyword,
            'location_code' => 2356,   // India
            'language_code' => 'en',
            'device'        => 'desktop',
            'os'            => 'windows',
            'depth'         => 100,
        ]]);

        $ch = curl_init('https://api.dataforseo.com/v3/serp/google/organic/live/advanced');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($login . ':' . $password),
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        $items = $data['tasks'][0]['result'][0]['items'] ?? [];

        foreach ($items as $item) {
            if (($item['type'] ?? '') !== 'organic') continue;
            $itemUrl    = $item['url'] ?? '';
            $itemDomain = parse_url($itemUrl, PHP_URL_HOST) ?: '';
            $itemDomain = str_replace('www.', '', strtolower($itemDomain));
            if (stripos($itemDomain, $domain) !== false ||
                stripos($itemUrl, $domain) !== false) {
                return (int)($item['rank_absolute'] ?? ($item['rank_group'] ?? 0));
            }
        }

        // If API returned results but domain not found → not in top 100
        if (!empty($items)) return 0;
    }

    // ── Method 2: Bing scraping fallback ─────────────────────
    $bingUrl = 'https://www.bing.com/search?q=' . urlencode($keyword) . '&count=50';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $bingUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER     => ['Accept-Language: en-US,en;q=0.9'],
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html) {
        preg_match_all('/<h2><a[^>]+href="([^"]+)"/i', $html, $m);
        $links = $m[1] ?? [];
        if (empty($links)) {
            preg_match_all('/href="([^"]+)"/i', $html, $m2);
            $links = $m2[1] ?? [];
        }
        
        $pos = 1;
        foreach ($links as $link) {
            if (strpos($link, 'http') !== 0) continue;
            if (strpos($link, 'bing.com') !== false || strpos($link, 'microsoft.com') !== false || strpos($link, 'live.com') !== false || strpos($link, 'go.microsoft.com') !== false) {
                continue;
            }
            
            $ld = str_replace('www.', '', strtolower(parse_url($link, PHP_URL_HOST) ?: ''));
            if (stripos($ld, $domain) !== false) {
                return $pos;
            }
            $pos++;
        }
    }

    return 0;
}

/**
 * Generate a random anchor text for a given keyword and business name to ensure Anchor Text Diversity.
 */
function getRandomAnchorText(string $keyword, string $businessName, string $websiteUrl = ''): string {
    $keyword = trim($keyword);
    $cleanUrl = '';
    if (!empty($websiteUrl)) {
        $cleanUrl = preg_replace('/^https?:\/\/(www\.)?/', '', rtrim($websiteUrl, '/'));
    }
    $branding = !empty($businessName) ? $businessName : (!empty($cleanUrl) ? $cleanUrl : 'our website');
    
    // Weighted selection of anchor types:
    // 20% Exact Match, 30% Branding, 25% Generic, 25% LSI / Partial Match
    $rand = rand(1, 100);
    
    if ($rand <= 20) {
        return ucwords($keyword);
    } elseif ($rand <= 50) {
        return $branding;
    } elseif ($rand <= 75) {
        $genericOptions = ["Click Here", "Visit Website", "Learn More", "Read More", "Official Website", "Check this resource"];
        return $genericOptions[array_rand($genericOptions)];
    } else {
        $lsiOptions = [
            "Best " . ucwords($keyword) . " Guide",
            "Professional " . ucwords($keyword),
            "Expert " . ucwords($keyword) . " Services",
            "About " . ucwords($keyword),
            "Find out more about " . ucwords($keyword)
        ];
        return $lsiOptions[array_rand($lsiOptions)];
    }
}

/**
 * Generate three diverse anchor texts (intro, mid, outro) for a single article.
 */
function getDiverseAnchorTexts(string $keyword, string $businessName, string $websiteUrl = ''): array {
    $cleanUrl = '';
    if (!empty($websiteUrl)) {
        $cleanUrl = preg_replace('/^https?:\/\/(www\.)?/', '', rtrim($websiteUrl, '/'));
    }
    $branding = !empty($businessName) ? $businessName : (!empty($cleanUrl) ? $cleanUrl : 'our website');
    
    // Intro: Exact Match
    $intro = ucwords($keyword);
    
    // Mid: LSI / Partial Match
    $lsiOptions = [
        "Best " . ucwords($keyword) . " Guide",
        "Professional " . ucwords($keyword),
        "Expert " . ucwords($keyword) . " Services",
        "About " . ucwords($keyword)
    ];
    $mid = $lsiOptions[array_rand($lsiOptions)];
    
    // Outro: Generic / Branding / URL
    $genericOptions = ["Click Here", "Visit Website", "Learn More", "Read More", $branding];
    if (!empty($websiteUrl)) {
        $genericOptions[] = $websiteUrl;
    }
    $outro = $genericOptions[array_rand($genericOptions)];
    
    return [
        'intro' => $intro,
        'mid'   => $mid,
        'outro' => $outro
    ];
}

/**
 * Verifies if a given backlink URL is indexed by Google using Custom Search Engine API.
 * Returns: 'indexed', 'not_indexed', or 'unchecked' (on API limits/errors).
 */
function checkUrlGoogleIndexing(string $url): string {
    $apiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';
    $cseCx  = defined('GOOGLE_CSE_CX') ? GOOGLE_CSE_CX : '';
    
    if (empty($apiKey) || empty($cseCx)) {
        return 'unchecked';
    }
    
    // We search for the exact URL in quotes
    $query = '"' . $url . '"';
    $apiUrl = 'https://www.googleapis.com/customsearch/v1?key=' . urlencode($apiKey) . '&cx=' . urlencode($cseCx) . '&q=' . urlencode($query);
    
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $resp = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // Quota exceeded, invalid key, or network issue
        return 'unchecked';
    }
    
    $data = json_decode($resp, true);
    if (!empty($data['items'])) {
        // Double check if any item URL matches the backlink URL (ignoring http vs https vs trailing slash)
        $normalizedTarget = preg_replace('/^https?:\/\/(www\.)?/', '', rtrim($url, '/'));
        
        foreach ($data['items'] as $item) {
            $normalizedItemLink = preg_replace('/^https?:\/\/(www\.)?/', '', rtrim($item['link'] ?? '', '/'));
            if (strcasecmp($normalizedTarget, $normalizedItemLink) === 0) {
                return 'indexed';
            }
        }
    }
    
    return 'not_indexed';
}


