<?php
// Run once to fix storage symlink, then DELETE this file immediately
$target = dirname(__DIR__) . '/storage/app/public';
$link   = __DIR__ . '/storage';

if (is_link($link)) {
    echo "Symlink already exists → " . readlink($link);
} elseif (is_dir($link)) {
    // Move any files from the real directory into the actual storage target
    $moved = 0;
    $failed = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($link, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($files as $file) {
        $relativePath = substr($file->getPathname(), strlen($link) + 1);
        $dest = $target . DIRECTORY_SEPARATOR . $relativePath;
        if ($file->isDir()) {
            if (!is_dir($dest)) mkdir($dest, 0755, true);
        } else {
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
            if (copy($file->getPathname(), $dest)) {
                $moved++;
            } else {
                $failed++;
                echo "WARN: Could not copy " . $relativePath . "<br>";
            }
        }
    }

    // Remove the real directory recursively
    function rrmdir($dir) {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? rrmdir($path) : unlink($path);
        }
        return rmdir($dir);
    }

    if (rrmdir($link)) {
        echo "Moved $moved file(s) to storage, removed real directory.<br>";
        if (symlink($target, $link)) {
            echo "SUCCESS: Symlink created → $target";
        } else {
            echo "FAILED: Could not create symlink after removing directory. Check server permissions.";
        }
    } else {
        echo "ERROR: Could not remove real public/storage directory. Remove it manually via cPanel File Manager.";
    }
} else {
    if (symlink($target, $link)) {
        echo "SUCCESS: Symlink created → $target";
    } else {
        echo "FAILED: Could not create symlink. Check server permissions.";
    }
}
