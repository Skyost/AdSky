<?php

/**
 * Utils methods.
 */

class Utils {

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