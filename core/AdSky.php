<?php

require_once __DIR__ . '/lang/EnglishLanguage.php';

require_once __DIR__ . '/settings/AdSettings.php';
require_once __DIR__ . '/settings/MySQLSettings.php';
require_once __DIR__ . '/settings/PayPalSettings.php';
require_once __DIR__ . '/settings/PluginSettings.php';
require_once __DIR__ . '/settings/WebsiteSettings.php';

class AdSky {

    const APP_NAME = 'AdSky';
    const APP_VERSION = 'v0.1';
    const APP_WEBSITE = 'https://dev.bukkit.org/projects/adsky';
    const APP_DEBUG = true;

    private static $_instance;

    private $_pdo;
    private $_auth;

    private $_adSettings;
    private $_mySQLSettings;
    private $_payPalSettings;
    private $_pluginSettings;
    private $_websiteSettings;

    private $_language;

    public function __construct() {
        $this -> _language = new EnglishLanguage();
    }

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new AdSky();
        }

        return self::$_instance;
    }

    public function getPDO() {
        if($this -> _pdo == null) {
            $this -> _pdo = $this -> getMySQLSettings() -> constructPDO();
        }

        return $this -> _pdo;
    }

    public function getAuth() {
        if($this -> _auth == null) {
            $mySQLSettings = $this -> getMySQLSettings();
            $this -> _auth = new \Delight\Auth\Auth($this -> getPDO(), null, $mySQLSettings -> getMySQLTablesPrefixes(), $mySQLSettings -> isThrottlingEnabled());
        }

        return $this -> _auth;
    }

    public function isInstalled() {
        return
            $this -> hasAdSettings() &&
            $this -> hasMySQLSettings() &&
            $this -> hasPayPalSettings() &&
            $this -> hasPluginSettings() &&
            $this -> hasWebsiteSettings();
    }

    public function getAdSettings() {
        if($this -> _adSettings == null && $this -> hasAdSettings()) {
            $this -> _adSettings = new AdSettings();
        }

        return $this -> _adSettings;
    }

    public function hasAdSettings() {
        return file_exists(__DIR__ . '/settings/AdSettings.php');
    }

    public function getMySQLSettings() {
        if($this -> _mySQLSettings == null && $this -> hasMySQLSettings()) {
            $this -> _mySQLSettings = new MySQLSettings();
        }

        return $this -> _mySQLSettings;
    }

    public function hasMySQLSettings() {
        return file_exists(__DIR__ . '/settings/MySQLSettings.php');
    }

    public function getPayPalSettings() {
        if($this -> _payPalSettings == null && $this -> hasPayPalSettings()) {
            $this -> _payPalSettings = new PayPalSettings();
        }

        return $this -> _payPalSettings;
    }

    public function hasPayPalSettings() {
        return file_exists(__DIR__ . '/settings/PayPalSettings.php');
    }

    public function getPluginSettings() {
        if($this -> _pluginSettings == null && $this -> hasPluginSettings()) {
            $this -> _pluginSettings = new PluginSettings();
        }

        return $this -> _pluginSettings;
    }

    public function hasPluginSettings() {
        return file_exists(__DIR__ . '/settings/PluginSettings.php');
    }

    public function getWebsiteSettings() {
        if($this -> _websiteSettings == null && $this -> hasWebsiteSettings()) {
            $this -> _websiteSettings = new WebsiteSettings();
        }

        return $this -> _websiteSettings;
    }

    public function hasWebsiteSettings() {
        return file_exists(__DIR__ . '/settings/WebsiteSettings.php');
    }

    public function getLanguage() {
        return $this -> _language;
    }

    public function getLanguageString($key) {
        return $this -> getLanguage() -> getSettings($key);
    }

    public function buildSettingsArray($settingsToAdd = []) {
        $result = [
            'APP_NAME' => AdSky::APP_NAME,
            'APP_VERSION' => AdSky::APP_VERSION,
            'APP_WEBSITE' => AdSky::APP_WEBSITE
        ];

        foreach($settingsToAdd as $settings) {
            foreach($settings -> getSettingsArray() as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

}