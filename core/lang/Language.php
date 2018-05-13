<?php

namespace AdSky\Core\Lang;

use AdSky\Core\Settings\Settings;

require_once __DIR__ . '/../Autoloader.php';

/**
 * Represents a language.
 */

abstract class Language extends Settings {

    const STRING_NOT_FOUND = 'Translation not found ("%s").';

    abstract public function getLanguage();

    /**
     * Gets the corresponding message according to the specified key.
     *
     * @param string $key The key.
     * @return string The message.
     */

    public function getSettings($key) {
        if(!array_key_exists($key, $this -> data)) {
            return sprintf(self::STRING_NOT_FOUND, $key);
        }
        return parent::getSettings($key);
    }

}