<?php

require_once __DIR__ . '/lang/EnglishLanguage.php';

require_once __DIR__ . '/settings/AdSettings.php';
require_once __DIR__ . '/settings/MySQLSettings.php';
require_once __DIR__ . '/settings/PayPalSettings.php';
require_once __DIR__ . '/settings/PluginSettings.php';
require_once __DIR__ . '/settings/WebsiteSettings.php';

require_once __DIR__ . '/objects/User.php';

/**
 * Core application class.
 */

class AdSky {

    const APP_NAME = 'AdSky';
    const APP_VERSION = 'v0.1';
    const APP_WEBSITE = 'https://github.com/Skyost/AdSky';
    const APP_DEBUG = true;

    private static $_instance;

    private $_medoo;
    private $_auth;

    private $_adSettings;
    private $_mySQLSettings;
    private $_payPalSettings;
    private $_pluginSettings;
    private $_websiteSettings;

    private $_language;

    /**
     * Constructs a new AdSky instance.
     */

    public function __construct() {
        $this -> _language = new EnglishLanguage();
    }

    /**
     * Gets the current AdSky instance.
     *
     * @return AdSky The current AdSky instance.
     */

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new AdSky();
        }

        return self::$_instance;
    }

    /**
     * Gets the current Medoo instance.
     *
     * @return \Medoo\Medoo The current Medoo instance.
     */

    public function getMedoo() {
        if($this -> _medoo == null) {
            $this -> _medoo = $this -> getMySQLSettings() -> constructMedoo();
        }

        return $this -> _medoo;
    }

    /**
     * Gets the current PDO instance.
     *
     * @return PDO The current PDO instance.
     */

    public function getPDO() {
        return $this -> getMedoo() -> pdo;
    }

    /**
     * Gets the current PHP-Auth instance.
     *
     * @return \Delight\Auth\Auth The current PHP-Auth instance.
     */

    public function getAuth() {
        if($this -> _auth == null) {
            $mySQLSettings = $this -> getMySQLSettings();
            $this -> _auth = new \Delight\Auth\Auth($this -> getPDO(), null, $mySQLSettings -> getMySQLTablesPrefixes(), $mySQLSettings -> isThrottlingEnabled());
        }

        return $this -> _auth;
    }

    /**
     * Gets the current user object.
     *
     * @return null|User The current user object or null if the user is not logged in.
     */

    public function getCurrentUserObject() {
        $auth = $this -> getAuth();
        if(!$auth -> isLoggedIn()) {
            return null;
        }

        return new User($auth);
    }

    /**
     * Checks if AdSky is installed.
     *
     * @return bool Whether AdSky is installed.
     */

    public function isInstalled() {
        return
            $this -> hasAdSettings() &&
            $this -> hasMySQLSettings() &&
            $this -> hasPayPalSettings() &&
            $this -> hasPluginSettings() &&
            $this -> hasWebsiteSettings();
    }

    /**
     * Gets the ad settings.
     *
     * @return AdSettings The ad settings.
     */

    public function getAdSettings() {
        if($this -> _adSettings == null && $this -> hasAdSettings()) {
            $this -> _adSettings = new AdSettings();
        }

        return $this -> _adSettings;
    }

    /**
     * Checks if the ad settings file exists.
     *
     * @return bool Whether the ad settings file exists.
     */

    public function hasAdSettings() {
        return file_exists(__DIR__ . '/settings/AdSettings.php');
    }

    /**
     * Gets the MySQL settings.
     *
     * @return MySQLSettings The MySQL settings.
     */

    public function getMySQLSettings() {
        if($this -> _mySQLSettings == null && $this -> hasMySQLSettings()) {
            $this -> _mySQLSettings = new MySQLSettings();
        }

        return $this -> _mySQLSettings;
    }

    /**
     * Checks if the MySQL settings file exists.
     *
     * @return bool Whether the MySQL settings file exists.
     */

    public function hasMySQLSettings() {
        return file_exists(__DIR__ . '/settings/MySQLSettings.php');
    }

    /**
     * Gets the PayPal settings.
     *
     * @return PayPalSettings The PayPal settings.
     */

    public function getPayPalSettings() {
        if($this -> _payPalSettings == null && $this -> hasPayPalSettings()) {
            $this -> _payPalSettings = new PayPalSettings();
        }

        return $this -> _payPalSettings;
    }

    /**
     * Checks if the PayPal settings file exists.
     *
     * @return bool Whether the PayPal settings file exists.
     */

    public function hasPayPalSettings() {
        return file_exists(__DIR__ . '/settings/PayPalSettings.php');
    }

    /**
     * Gets the plugin settings.
     *
     * @return PluginSettings The plugin settings.
     */

    public function getPluginSettings() {
        if($this -> _pluginSettings == null && $this -> hasPluginSettings()) {
            $this -> _pluginSettings = new PluginSettings();
        }

        return $this -> _pluginSettings;
    }

    /**
     * Checks if the plugin settings file exists.
     *
     * @return bool Whether the plugin settings file exists.
     */

    public function hasPluginSettings() {
        return file_exists(__DIR__ . '/settings/PluginSettings.php');
    }

    /**
     * Gets the website settings.
     *
     * @return WebsiteSettings The website settings.
     */

    public function getWebsiteSettings() {
        if($this -> _websiteSettings == null && $this -> hasWebsiteSettings()) {
            $this -> _websiteSettings = new WebsiteSettings();
        }

        return $this -> _websiteSettings;
    }

    /**
     * Checks if the website settings file exists.
     *
     * @return bool Whether the website settings file exists.
     */

    public function hasWebsiteSettings() {
        return file_exists(__DIR__ . '/settings/WebsiteSettings.php');
    }

    /**
     * Gets the current language.
     *
     * @return EnglishLanguage The current language.
     */

    public function getLanguage() {
        return $this -> _language;
    }

    /**
     * Gets a language message according to its key.
     *
     * @param string $key The key.
     *
     * @return string The language message.
     */

    public function getLanguageString($key) {
        return $this -> getLanguage() -> getSettings($key);
    }

    /**
     * Creates an array of settings.
     *
     * @param array $settingsToAdd All settings.
     *
     * @return array The array of settings.
     */

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