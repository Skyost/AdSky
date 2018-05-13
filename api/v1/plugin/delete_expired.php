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
    $adsky -> getPDO() -> query('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))') -> execute();
    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(PDOException $error) {
    Response::createAndReturn('API_ERROR_INVALID_PLUGIN_KEY', null, $error);
}