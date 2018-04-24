<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/renew.php
 * Target : Ads
 * User role : User
 * Description : Renew an ad.
 * Throttle : 5 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P] days : Number of days to add to the current expiration date.
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

use Delight\Auth;

try {
    // We check if all arguments are okay.
    if(!isset($_POST['type']) || strlen($_POST['type']) === Ad::TYPE_TITLE || empty($_POST['title']) || empty($_POST['days'])) {
        (new Response($adsky -> getLanguage() -> formatNotSet([$adsky -> getLanguageString('API_ERROR_NOT_SET_TYPE'), $adsky -> getLanguageString('API_ERROR_NOT_SET_TITLE'), $adsky -> getLanguageString('API_ERROR_NOT_SET_DAYS')]))) -> returnResponse();
    }

    if($_POST['days'] <= 0) {
        (new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'))) -> returnResponse();
    }

    $adsky = AdSky::getInstance();
    $auth = $adsky -> getAuth();

    // Throttle protection.
    $auth -> throttle([
        'ad-renew',
        $_SERVER['REMOTE_ADDR']
    ], 5, 60);

    // We check if the user is logged in.
    $user = User::isLoggedIn() -> _object;

    if($user == null) {
        (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
    }

    // Okay, now we can select our ad.
    $medoo = $adsky -> getMedoo();
    $row = $medoo -> select($adsky -> getMySQLSettings() -> getAdsTable(), ['interval', 'until'], ['title' => $_POST['title']]);
    if(empty($row)) {
        (new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'))) -> returnResponse();
    }

    // We check if the days parameter is good.
    $max = ($_POST['type'] == Ad::TYPE_TITLE ? $adSettings -> getTitleAdMaximumExpiration() : $adSettings -> getChatAdMaximumExpiration()) - (($row['expiration'] - mktime(0, 0, 0)) / (60 * 60 * 24));
    $min = ($_POST['type'] == Ad::TYPE_TITLE ? $adSettings -> getTitleAdMinimumExpiration() : $adSettings -> getChatAdMaximumExpiration());

    if($_POST['days'] < $min || $_POST['days'] > $max) {
        (new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'))) -> returnResponse();
    }

    // So now, we are going to create the ad.
    $type = intval($_POST['type']);
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();

    $adSettings = $adsky -> getAdSettings();

    $ad = new Ad($user['username'], $type, $_POST['title'], null, $row['interval'], $row['until']);

    // If the user is an admin, we don't have to use the PayPal API.
    if($user['type'] == 0) {
        $response = $ad -> renew($_POST['days']);
        if($response -> _error != null) {
            $response -> returnResponse();
        }

        (new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'), $root . 'admin/?message=renew_success#list')) -> returnResponse();
    }

    // Otherwise, let's create a payment !
    $url = $root . 'payment/renew/?' . http_build_query($_POST);
    (new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $adsky -> getPayPalSettings() -> createApprovalLink($url, $type, $row['interval'], $_POST['days']))) -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    (new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error)) -> returnResponse();
}
catch(Auth\AuthError $error) {
    (new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error)) -> returnResponse();
}
catch(PDOException $error) {
    (new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error)) -> returnResponse();
}
catch(Exception $ex) {
    (new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'), null, $error)) -> returnResponse();
}