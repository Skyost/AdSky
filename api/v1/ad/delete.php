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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Response;
use AdSky\Core\Utils;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // Throttle protection.
    $adsky -> getAuth() -> throttle([
        'ad-delete',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    // We check if the user is logged-in.
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // We get the ad ID.
    if(Utils::trueEmpty($_POST, 'id')) {
        Response::createAndReturn(['API_ERROR_NOT_SET_ID']);
    }

    // Now we can get our ad.
    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        Response::createAndReturn('API_ERROR_AD_NOT_FOUND');
    }

    // If the user is not the owner of the ad and is not admin, we send an error.
    if($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // We delete the ad.
    $ad -> setDeleted();
    $ad -> sendUpdateToDatabase($_POST['id']);

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Delight\Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Delight\Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}