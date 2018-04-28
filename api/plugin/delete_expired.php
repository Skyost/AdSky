<?php

/**
 * ADSKY API FILE
 *
 * Name : plugin/delete_expired.php
 * Target : Ads
 * User role : None
 * Description : Delete all expired ads.
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
    $adsky -> getPDO() -> query('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))') -> execute();
    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}