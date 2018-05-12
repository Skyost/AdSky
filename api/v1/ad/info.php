<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/info.php
 * Target : Ads
 * User role : User
 * Description : Get information about an ad. You must be an admin to see other's ad data.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] id : Ad ID.
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/Ad.php';

require_once __DIR__ . '/../../../core/Response.php';

require_once __DIR__ . '/../../../core/Utils.php';

$adsky = AdSky::getInstance();

try {
    // Throttle protection.
    $adsky -> getAuth() -> throttle([
        'ad-info',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    $language = $adsky -> getLanguage();

    // We get the ID.
    if(Utils::trueEmpty($_POST, 'id')) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_ID')]));
        $response -> returnResponse();
    }

    // Now we can get our ad.
    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        $response = new Response($language -> getSettings('API_ERROR_AD_NOT_FOUND'));
        $response -> returnResponse();
    }

    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || ($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin())) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $ad -> toArray());
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