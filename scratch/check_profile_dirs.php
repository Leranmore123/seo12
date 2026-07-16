<?php
$dir = dirname(__DIR__) . '/selenium';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "=== CHROME PROFILE DIRECTORIES & OWNERS ===\n";
    foreach ($files as $f) {
        if (strpos($f, 'chrome_profile_') === 0 || strpos($f, 'tmp_dir_') === 0) {
            $path = $dir . '/' . $f;
            $ownerId = fileowner($path);
            $ownerName = 'unknown';
            if (function_exists('posix_getpwuid')) {
                $ownerName = posix_getpwuid($ownerId)['name'] ?? 'unknown';
            }
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            echo "Dir: {$f} | Owner: {$ownerName} (UID: {$ownerId}) | Perms: {$perms}\n";
        }
    }
} else {
    echo "Selenium directory does not exist!\n";
}
