<?php
header('Content-Type: text/plain');

$localFile = dirname(__DIR__) . '/config.local.php';
if (!file_exists($localFile)) {
    echo "Creating new config.local.php...\n";
    $config = [];
} else {
    $config = include $localFile;
    if (!is_array($config)) {
        $config = [];
    }
}

// Add the Google API credentials from the workspace
$config['GOOGLE_API_KEY'] = 'AIzaSyCrBv2ea6EpXNxcfAUVLniv2MUdD27A8FU';
$config['GOOGLE_CSE_CX']  = '41ddc4f081ac24b52';

$content = "<?php\n// Auto-merged by Antigravity key updater — " . date('Y-m-d H:i:s') . "\nreturn " . var_export($config, true) . ";\n";

if (file_put_contents($localFile, $content)) {
    echo "=== GOOGLE API KEYS SUCCESSFULLY APPLIED TO AWS SERVER ===\n";
    echo "GOOGLE_API_KEY and GOOGLE_CSE_CX have been saved in config.local.php!\n";
} else {
    echo "Error writing to config.local.php. Check permissions.\n";
}
