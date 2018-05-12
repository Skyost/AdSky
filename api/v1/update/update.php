<?php

/**
 * ADSKY API FILE
 *
 * Name : update/update.php
 * Target : AdSky
 * User role : Admin
 * Description : Allows to update AdSky (if available).
 * Throttle : 1 request per hour.
 *
 * Parameters :
 * None.
 */

require_once __DIR__ . '/../../../core/AdSky.php';

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/objects/GithubUpdater.php';

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
        'update-update',
        $_SERVER['REMOTE_ADDR']
    ], 1, 3600);

    // Then we update.
    $updater = new GithubUpdater();
    if(!$updater -> update()) {
        $response = new Response($adsky -> getLanguageString('API_UPDATE_ERROR'));
        $response -> returnResponse();
    }

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
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