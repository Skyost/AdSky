<?php

namespace AdSky\Core;

use AdSky\Core\Lang\EnglishLanguage;
use AdSky\Core\Objects\User;
use Delight\Auth\Auth;
use PDO;

require_once __DIR__ . '/Autoloader.php';

/**
 * Core application class.
 */

class AdSky {

    const APP_NAME = 'AdSky';
    const APP_VERSION = 'v0.1';
    const APP_WEBSITE = 'https://github.com/Skyost/AdSky';
    const APP_DEBUG = false;

    private static $instance;

    private $medoo;
    private $auth;

    private $adSettings;
    private $mySQLSettings;
    private $payPalSettings;
    private $pluginSettings;
    private $websiteSettings;

    private $language;

    /**
     * Constructs a new AdSky instance.
     */

    public function __construct() {
        Autoloader::register();

        $this -> language = new EnglishLanguage();
    }

    /**
     * Gets the current AdSky instance.
     *
     * @return AdSky The current AdSky instance.
     */

    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new AdSky();
        }

        return self::$instance;
    }

    /**
     * Gets the current Medoo instance.
     *
     * @return \Medoo\Medoo The current Medoo instance.
     */

    public function getMedoo() {
        if(!$this -> hasMySQLSettings()) {
            return null;
        }

        if($this -> medoo == null) {
            $this -> medoo = $this -> getMySQLSettings() -> constructMedoo();
        }

        return $this -> medoo;
    }

    /**
     * Gets the current PDO instance.
     *
     * @return PDO The current PDO instance.
     */

    public function getPDO() {
        $medoo = $this -> getMedoo();
        if($medoo == null) {
            return null;
        }

        return $medoo -> pdo;
    }

    /**
     * Gets the current PHP-Auth instance.
     *
     * @return \Delight\Auth\Auth The current PHP-Auth instance.
     */

    public function getAuth() {
        if(!$this -> hasMySQLSettings()) {
            return null;
        }

        if($this -> auth == null) {
            $mySQLSettings = $this -> getMySQLSettings();
            $this -> auth = new Auth($this -> getPDO(), null, $mySQLSettings -> getMySQLTablesPrefixes(), $mySQLSettings -> isThrottlingEnabled());
        }

        return $this -> auth;
    }

    /**
     * Gets the current user object.
     *
     * @return null|User The current user object or null if the user is not logged in.
     */

    public function getCurrentUserObject() {
        $auth = $this -> getAuth();
        if($auth == null || !$auth -> isLoggedIn()) {
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
     * @return Settings\AdSettings The ad settings.
     */

    public function getAdSettings() {
        if($this -> adSettings == null && $this -> hasAdSettings()) {
            $this -> adSettings = new Settings\AdSettings();
        }

        return $this -> adSettings;
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
     * @return Settings\MySQLSettings The MySQL settings.
     */

    public function getMySQLSettings() {
        if($this -> mySQLSettings == null && $this -> hasMySQLSettings()) {
            require_once __DIR__ . '/settings/MySQLSettings.php';

            $this -> mySQLSettings = new Settings\MySQLSettings();
        }

        return $this -> mySQLSettings;
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
     * @return Settings\PayPalSettings The PayPal settings.
     */

    public function getPayPalSettings() {
        if($this -> payPalSettings == null && $this -> hasPayPalSettings()) {
            require_once __DIR__ . '/settings/PayPalSettings.php';

            $this -> payPalSettings = new Settings\PayPalSettings();
        }

        return $this -> payPalSettings;
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
     * @return Settings\PluginSettings The plugin settings.
     */

    public function getPluginSettings() {
        if($this -> pluginSettings == null && $this -> hasPluginSettings()) {
            require_once __DIR__ . '/settings/PluginSettings.php';

            $this -> pluginSettings = new Settings\PluginSettings();
        }

        return $this -> pluginSettings;
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
     * @return Settings\WebsiteSettings The website settings.
     */

    public function getWebsiteSettings() {
        if($this -> websiteSettings == null && $this -> hasWebsiteSettings()) {
            require_once __DIR__ . '/settings/WebsiteSettings.php';

            $this -> websiteSettings = new Settings\WebsiteSettings();
        }

        return $this -> websiteSettings;
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
        return $this -> language;
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