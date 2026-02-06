<?php
// Clear ThinkPHP Cache
$path = __DIR__ . '/../source/runtime';

function delDirAndFile($path, $delDir = false) {
    if (is_array($path)) {
        foreach ($path as $subPath)
            delDirAndFile($subPath, $delDir);
    }
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    clearstatcache();
}

delDirAndFile($path . '/temp');
delDirAndFile($path . '/cache');
delDirAndFile($path . '/log');
delDirAndFile($path . '/schema'); 

echo "Cache cleared successfully.\n";
