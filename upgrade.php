<?php

foreach(scandir(__DIR__) as $file) {
    if($file == '.' || $file == '..' || $file == 'upgrade.php') {
        continue;
    }

    copyDirectory(__DIR__ . '/' . $file, dirname(__DIR__) . '/' . $file);
}

/**
 * Copies a file / directory.
 *
 * @param string $source The source directory.
 * @param string $destination The destination file.
 */

function copyDirectory($source, $destination) {
    if(is_file($source)) {
        copy($source, $destination);
        return;
    }

    if(is_dir($source)) {
        mkdir($destination, 0700);
        foreach(scandir($source) as $file) {
            if($file != '.' && $file != '..') {
                copyDirectory($source . '/' . $file, $destination . '/' . $file);
            }
        }
        return;
    }

    if(is_link($source)) {
        symlink(readlink($source), $destination);
    }
}