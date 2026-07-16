<?php
$file = dirname(__DIR__) . '/selenium/pinterest_error.png';
if (file_exists($file)) {
    echo "File owner ID: " . fileowner($file) . "\n";
    if (unlink($file)) {
        echo "Successfully deleted old screenshot!\n";
    } else {
        echo "Failed to delete old screenshot! Please delete it manually using: sudo rm {$file}\n";
    }
} else {
    echo "Screenshot file does not exist.\n";
}
