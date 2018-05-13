<?php

namespace AdSky\Core\Settings;

/**
 * A key-value Settings class.
 */

class Settings {

    protected $data = [];

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