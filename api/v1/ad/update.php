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
        'ad-update',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    $language = $adsky -> getLanguage();

    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // We get the ID.
    if(Utils::trueEmpty($_POST, 'id')) {
        Response::createAndReturn(['API_ERROR_NOT_SET_ID']);
    }

    // Now we can get our ad.
    $ad = Ad::getFromDatabase($_POST['id']);

    if($ad == null) {
        Response::createAndReturn('API_ERROR_AD_NOT_FOUND');
    }

    // And we can update it.
    $type = Utils::notEmptyOrNull($_POST, 'type');
    $title = Utils::notEmptyOrNull($_POST, 'title');
    $message = Utils::notEmptyOrNull($_POST, 'message');
    $interval = Utils::notEmptyOrNull($_POST, 'interval');
    $expiration = Utils::notEmptyOrNull($_POST, 'expiration');
    $duration = Utils::notEmptyOrNull($_POST, 'duration');

    if($type != null && !$ad -> setType($type)) {
        Response::createAndReturn('API_ERROR_INVALID_TYPE');
    }

    if($title != null && !$ad -> setTitle($title)) {
        Response::createAndReturn('API_ERROR_INVALID_TITLE');
    }

    if($message != null && !$ad -> setMessage($message)) {
        Response::createAndReturn('API_ERROR_INVALID_MESSAGE');
    }

    if($interval != null && !$ad -> setInterval($interval)) {
        Response::createAndReturn('API_ERROR_INVALID_INTERVAL');
    }

    if($expiration != null && !$ad -> setExpiration($expiration)) {
        Response::createAndReturn('API_ERROR_INVALID_EXPIRATIONDATE');
    }

    if($duration != null && !$ad -> setDuration($duration)) {
        Response::createAndReturn('API_ERROR_INVALID_DURATION');
    }

    $ad -> sendUpdateToDatabase($_POST['id']);

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Delight\Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Delight\Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}