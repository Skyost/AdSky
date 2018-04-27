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

try {
    $adsky = AdSky::getInstance();
    $adsky -> getAuth() -> throttle([
        'ad-delete',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    $language = $adsky -> getLanguage();

    if(!isset($_POST['id'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_ID')]));
        $response -> returnResponse();
    }

    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_AD_NOT_FOUND')]));
        $response -> returnResponse();
    }

    if($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin()) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    $ad -> setDeleted();
    $ad -> sendUpdateToDatabase($_POST['id']);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}