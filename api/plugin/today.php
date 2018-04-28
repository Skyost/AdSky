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

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();

try {
    // We try to validate the sent key.
    if(empty($_POST['key']) || $_POST['key'] != $adsky -> getPluginSettings() -> getPluginKey()) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_PLUGIN_KEY'));
        $response -> returnResponse();
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

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $object);
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}