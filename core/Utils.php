<?php

namespace AdSky\Core;

/**
 * Utils methods.
 */

class Utils {

    /**
     * Deletes a file or a folder.
     *
     * @param string $dir The directory.
     *
     * @return bool Whether this is a success.
     */

    public static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach($files as $file) {
            (is_dir($dir . '/' . $file)) ? self::delTree($dir . '/' . $file) : unlink($dir . '/' . $file);
        }
        return rmdir($dir);
    }

    /**
     * If $array[$index] is not empty, then returns it. null otherwise.
     *
     * @param array $array The array.
     * @param mixed $index The index.
     *
     * @return mixed if $array[$index] is not empty, then returns it. null otherwise.
     */

    public static function notEmptyOrNull($array, $index) {
        return empty($array[$index]) ? null : $array[$index];
    }

    /**
     * Checks if a var is empty (and allowing 0 to work).
     *
     * @param array $array The array.
     * @param mixed $index The index.
     *
     * @return bool Whether the variable is empty.
     */

    public static function trueEmpty($array, $index) {
        return !isset($array[$index]) || strlen($array[$index]) === 0;
    }

    /**
     * Replaces separators by slashes in a String.
     *
     * @param string $string The String.
     *
     * @return string The new String.
     */

    public static function separatorsToSlashes($string) {
        return str_replace(DIRECTORY_SEPARATOR,'/', $string);
    }

    /**
     * Checks if a String ends with another.
     *
     * @param string $haystack The string.
     * @param string $needle The suffix.
     *
     * @return bool Whether the String ends with the suffix.
     */

    public static function endsWith($haystack, $needle) {
        return substr($haystack, -strlen($needle)) === $needle;
    }

}