<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/update.php
 * Target : Ads
 * User role : Admin
 * Description : Update an ad. An admin can update anyone's ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] oldtype : Current type of the ad (Title / Chat).
 * [P] oldtitle : Current title of the ad.
 * [P] username : Owner of the ad.
 * [P][O] type : New type of the ad (Title / Chat).
 * [P][O] title : New title of the ad.
 * [P][O] message : New message of the ad.
 * [P][O] interval : New interval (times to display per day) of the ad.
 * [P][O] expiration : New expiration of the ad (in timestamp).
 * [P][O] duration : New duration of the ad (for Title ads).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

try {
    $adsky = AdSky::getInstance();
    $adsky -> getAuth() -> throttle([
        'ad-update',
        $_SERVER['REMOTE_ADDR']
    ], 10, 60);

    $language = $adsky -> getLanguage();

    $object = User::isLoggedIn() -> _object;

    if($object == null || $object['type'] != 0) {
        $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    if((!isset($_POST['oldtype']) || strlen($_POST['oldtype']) === 0) || empty($_POST['oldtitle']) || empty($_POST['username'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDTITLE'), $language -> getSettings('API_ERROR_NOT_SET_OLDTYPE'), $language -> getSettings('API_ERROR_NOT_SET_USERNAME')]));
        $response -> returnResponse();
    }

    if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != Ad::TYPE_TITLE && $_POST['type'] != Ad::TYPE_CHAT)) {
        $response =  new Response($language -> getSettings('API_ERROR_INVALID_TYPE'));
        $response -> returnResponse();
    }

    $response = (new Ad($_POST['username'], $_POST['oldtype'], $_POST['oldtitle'])) -> update(Utils::notEmptyOrNull($_POST, 'type'), Utils::notEmptyOrNull($_POST, 'title'), Utils::notEmptyOrNull($_POST, 'message'), Utils::notEmptyOrNull($_POST, 'interval'), Utils::notEmptyOrNull($_POST, 'expiration'), Utils::notEmptyOrNull($_POST, 'duration'));
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