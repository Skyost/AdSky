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

require_once __DIR__ . '/../../core/AdSky.php';

require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/objects/GithubUpdater.php';

$adsky = AdSky::getInstance();

try {
    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || !$user -> isAdmin()) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
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
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Exception $ex) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'), null, $error);
    $response -> returnResponse();
}