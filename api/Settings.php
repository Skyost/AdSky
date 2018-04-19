<?php

require_once __DIR__ . '/../vendor/autoload.php';

$settings = [];

$installed =
    file_exists(__DIR__ . '/settings/Ad.php') &&
    file_exists(__DIR__ . '/settings/MySQL.php') &&
    file_exists(__DIR__ . '/settings/Others.php') &&
    file_exists(__DIR__ . '/settings/PayPal.php') &&
    file_exists(__DIR__ . '/settings/Plugin.php') &&
    file_exists(__DIR__ . '/settings/Website.php');

if($installed) {
    include __DIR__ . '/settings/Ad.php';
    include __DIR__ . '/settings/MySQL.php';
    include __DIR__ . '/settings/Others.php';
    include __DIR__ . '/settings/PayPal.php';
    include __DIR__ . '/settings/Plugin.php';
    include __DIR__ . '/settings/Website.php';
}

if(!empty($settings['APP_DEBUG']) && $settings['APP_DEBUG']) {
    $_POST = array_merge($_POST, $_GET);
}

function getPayPalAPI() {
    global $settings;
    return new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $settings['PAYPAL_CLIENT_ID'],
            $settings['PAYPAL_CLIENT_SECRET']
        )
    );
}

function getPDO() {
    try {
        global $settings;
        return new \PDO('mysql:host=' . $settings['DB_HOST'] . ';port=' . $settings['DB_PORT'] . ';dbname=' . $settings['DB_NAME'] . ';charset=utf8mb4', $settings['DB_USER'], $settings['DB_PASSWORD']);
    }
    catch(PDOException $error) {
        die('Unable to connect to MySQL database. Please check your settings in api/settings/MySQL.php.');
    }
}

function createAuth($pdo = null) {
    if($pdo == null) {
        $pdo = getPDO();
    }

    global $settings;
    return new \Delight\Auth\Auth($pdo, null, $settings['DB_PREFIX'], $settings['DB_THROTTLING']);
}

function utilNotEmptyOrNull($array, $index) {
    return empty($array[$index]) ? null : $array[$index];
}