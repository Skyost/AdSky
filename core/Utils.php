<?php

class Utils {

    public static function notEmptyOrNull($array, $index) {
        return empty($array[$index]) ? null : $array[$index];
    }

    public static function separatorsToSlashes($string) {
        return str_replace(DIRECTORY_SEPARATOR,'/', $string);
    }

    public static function endsWith($haystack, $needle) {
        return substr($haystack, -strlen($needle)) === $needle;
    }

}