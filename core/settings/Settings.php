<?php

namespace AdSky\Core\Settings;

/**
 * A key-value Settings class.
 */

class Settings {

    protected $data = [];

    /**
     * Checks if a key exists in the settings.
     *
     * @param mixed $key The key.
     *
     * @return bool Whether the key exists in the settings.
     */

    public function has($key) {
        return array_key_exists($key, $this -> data);
    }

    /**
     * Puts some settings.
     *
     * @param mixed $key Key.
     * @param mixed $value Value.
     */

    protected function putSettings($key, $value) {
        $this -> data[$key] = $value;
    }

    /**
     * Gets a value corresponding to its key.
     *
     * @param mixed $key The key.
     *
     * @return mixed The value.
     */

    public function getSettings($key) {
        return $this -> data[$key];
    }

    /**
     * Gets a key: value array.
     *
     * @return array The key: value array.
     */

    public function getSettingsArray() {
        return $this -> data;
    }

}