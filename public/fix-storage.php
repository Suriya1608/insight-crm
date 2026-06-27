<?php
// Run once to fix storage symlink, then DELETE this file immediately
$target = dirname(__DIR__) . '/storage/app/public';
$link   = __DIR__ . '/storage';

if (is_link($link)) {
    echo "Symlink already exists → " . readlink($link);
} elseif (is_dir($link)) {
    echo "ERROR: public/storage is a real directory. Remove it manually first.";
} else {
    if (symlink($target, $link)) {
        echo "SUCCESS: Symlink created → $target";
    } else {
        echo "FAILED: Could not create symlink. Check server permissions.";
    }
}
