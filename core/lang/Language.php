<?php

require __DIR__ . '/../settings/Settings.php';

/**
 * Represents a language.
 */

abstract class Language extends Settings {

    abstract public function getLanguage();

    /**
     * Gets the corresponding message according to the specified key.
     *
     * @param string $key The key.
     * @return string The message.
     */

    public function getSettings($key) {
        if(!in_array($key, $this -> _data)) {
            return 'Translation not found ("' . $key . '")."';
        }
        return parent::getSettings($key);
    }

}