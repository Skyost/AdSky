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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Response;
use AdSky\Core\Utils;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
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
        Response::createAndReturn(['API_ERROR_NOT_SET_ID']);
    }

    // Now we can get our ad.
    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        Response::createAndReturn('API_ERROR_AD_NOT_FOUND');
    }

    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || ($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin())) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Delight\Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Delight\Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}