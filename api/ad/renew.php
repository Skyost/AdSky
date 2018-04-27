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
 * [P] id : Ad ID.
 * [P] days : Number of days to add to the current expiration date.
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

require_once __DIR__ . '/../../core/Response.php';

use Delight\Auth;

try {
    $adsky = AdSky::getInstance();

    // We check if all arguments are okay.
    if(!isset($_POST['id']) || strlen($_POST['id']) === 0 || empty($_POST['days'])) {
        $response = new Response($adsky -> getLanguage() -> formatNotSet([$adsky -> getLanguageString('API_ERROR_NOT_SET_ID'), $adsky -> getLanguageString('API_ERROR_NOT_SET_DAYS')]));
        $response -> returnResponse();
    }

    $days = intval($_POST['days']);
    if($days <= 0) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'));
        $response -> returnResponse();
    }

    // Throttle protection.
    $auth = $adsky -> getAuth();
    $auth -> throttle([
        'ad-renew',
        $_SERVER['REMOTE_ADDR']
    ], 5, 60);

    // We check if the user is logged in.
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    // Okay, now we can select our ad.
    $ad = Ad::getFromDatabase(intval($_POST['id']));
    if($ad == null) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_AD_NOT_FOUND'));
        $response -> returnResponse();
    }

    // We check if the days parameter is good.
    if(!$ad -> renew($days)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'));
        $response -> returnResponse();
    }

    // If the user is an admin, we don't have to use the PayPal API.
    if($user -> isAdmin()) {
        $ad -> sendUpdateToDatabase($_POST['id']);

        $response = new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'), $root . 'admin/?message=renew_success#list');
        $response -> returnResponse();
    }

    // Otherwise, let's create a payment !
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();
    $url = $root . 'payment/renew/?' . http_build_query($_POST);
    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $adsky -> getPayPalSettings() -> createApprovalLink($url, $ad -> getType(), $ad -> getInterval(), $days));
    $response -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(Exception $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'), null, $error);
    $response -> returnResponse();
}