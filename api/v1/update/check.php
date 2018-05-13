<?php

/**
 * ADSKY API FILE
 *
 * Name : update/check.php
 * Target : AdSky
 * User role : Admin
 * Description : Allows to check for updates. If there is an update, the object will be an array containing the version and the download link.
 * Throttle : 5 requests per 60 seconds.
 *
 * Parameters :
 * None.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\GithubUpdater;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // Throttle protection.
    $auth = $adsky -> getAuth();
    $auth -> throttle([
        'update-check',
        $_SERVER['REMOTE_ADDR']
    ], 5, 60);

    // Then we check for updates.
    $updater = new GithubUpdater();
    $result = $updater -> check();

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));

    if($result != null) {
        $response -> setObject($result);
    }

    $response -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Exception $error) {
    Response::createAndReturn('API_UPDATE_ERROR', null, $error);
}