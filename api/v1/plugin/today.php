<?php

/**
 * ADSKY API FILE
 *
 * Name : plugin/today.php
 * Target : Ads
 * User role : None
 * Description : List all today's ads without pagination.
 * Throttle : None.
 *
 * Parameters :
 * [P] key : Plugin key.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Response;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // We try to validate the sent key.
    if(empty($_POST['key']) || $_POST['key'] != $adsky -> getPluginSettings() -> getPluginKey()) {
        Response::createAndReturn('API_ERROR_INVALID_PLUGIN_KEY');
    }

    // If everything is okay, we can query our database.
    $result = $adsky -> getPDO() -> query('SELECT * FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` > UNIX_TIMESTAMP(CONVERT_TZ(DATE(SUBDATE(NOW(), 1)), \'+00:00\', \'SYSTEM\'))');
    $object = [];

    foreach(($result -> fetchAll()) as $row) {
        array_push($object, [
            'username' => $row['username'],
            'type' => intval($row['type']),
            'title' => $row['title'],
            'message' => $row['message'],
            'interval' => intval($row['interval']),
            'expiration' => intval($row['until']),
            'duration' => intval($row['duration'])
        ]);
    }

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(PDOException $error) {
    Response::createAndReturn('API_ERROR_MYSQL_ERROR', null, $error);
}