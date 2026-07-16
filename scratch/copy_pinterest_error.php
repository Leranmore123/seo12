<?php
require_once __DIR__ . '/../config.php';

$src = dirname(__DIR__) . '/selenium/pinterest_error.png';
$dst = dirname(__DIR__) . '/uploads/pinterest_error.png';

echo "Source: {$src}\n";
echo "Dest: {$dst}\n";

if (file_exists($src)) {
    if (copy($src, $dst)) {
        chmod($dst, 0777);
        echo "Successfully copied screenshot to public uploads folder!\n";
        echo "You can view it at: http://<your-server-ip-or-domain>/uploads/pinterest_error.png\n";
    } else {
        echo "Failed to copy file!\n";
    }
} else {
    echo "Screenshot file does not exist! It means the script crashed before taking the screenshot or failed earlier.\n";
}
