<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();

if(empty($_POST['key']) || $_POST['key'] != $adsky -> getPluginSettings() -> getPluginKey()) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_PLUGIN_KEY'));
    $response -> returnResponse();
}

try {
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