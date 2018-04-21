<?php

class Utils {

    public static function notEmptyOrNull($array, $index) {
        return empty($array[$index]) ? null : $array[$index];
    }

}