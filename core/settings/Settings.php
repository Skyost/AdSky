<?php

class Settings {

    private $_data = [];

    protected function putSettings($key, $value) {
        $this -> _data[$key] = $value;
    }

    public function getSettings($key) {
        return $this -> _data[$key];
    }

    public function getSettingsArray() {
        return $this -> _data;
    }

}