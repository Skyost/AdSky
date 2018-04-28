<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/delete.php
 * Target : Ads
 * User role : User
 * Description : Delete an ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] id : Ad ID.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

try {
    // Throttle protection.
    $adsky -> getAuth() -> throttle([
        'ad-delete',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    // We check if the user is logged-in.
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    // We get the ad ID.
    if(!isset($_POST['id'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_ID')]));
        $response -> returnResponse();
    }

    // Now we can get our ad.
    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_AD_NOT_FOUND')]));
        $response -> returnResponse();
    }

    // If the user is not the owner of the ad and is not admin, we send an error.
    if($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin()) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    // We delete the ad.
    $ad -> setDeleted();
    $ad -> sendUpdateToDatabase($_POST['id']);

    $response = new Response(null, $language -> getSettings('API_SUCCESS'));
    $response -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($language -> getSettings('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}