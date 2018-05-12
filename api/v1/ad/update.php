<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/update.php
 * Target : Ads
 * User role : Admin
 * Description : Update an ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] id : Ad ID.
 * [P][O] type : New type of the ad (Title / Chat).
 * [P][O] title : New title of the ad.
 * [P][O] message : New message of the ad.
 * [P][O] interval : New interval (times to display per day) of the ad.
 * [P][O] expiration : New expiration of the ad (in timestamp).
 * [P][O] duration : New duration of the ad (for Title ads).
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/Ad.php';

require_once __DIR__ . '/../../../core/Utils.php';

require_once __DIR__ . '/../../../core/Response.php';

$adsky = AdSky::getInstance();

try {
    // Throttle protection.
    $adsky -> getAuth() -> throttle([
        'ad-update',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    $language = $adsky -> getLanguage();

    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || !$user -> isAdmin()) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

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

    // And we can update it.
    $type = Utils::notEmptyOrNull($_POST, 'type');
    $title = Utils::notEmptyOrNull($_POST, 'title');
    $message = Utils::notEmptyOrNull($_POST, 'message');
    $interval = Utils::notEmptyOrNull($_POST, 'interval');
    $expiration = Utils::notEmptyOrNull($_POST, 'expiration');
    $duration = Utils::notEmptyOrNull($_POST, 'duration');

    if($type != null && !$ad -> setType($type)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_TYPE'));
        $response -> returnResponse();
    }

    if($title != null && !$ad -> setTitle($title)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_TITLE'));
        $response -> returnResponse();
    }

    if($message != null && !$ad -> setMessage($message)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_MESSAGE'));
        $response -> returnResponse();
    }

    if($interval != null && !$ad -> setInterval($interval)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_INTERVAL'));
        $response -> returnResponse();
    }

    if($expiration != null && !$ad -> setExpiration($expiration)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_EXPIRATIONDATE'));
        $response -> returnResponse();
    }

    if($duration != null && !$ad -> setDuration($duration)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_DURATION'));
        $response -> returnResponse();
    }

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