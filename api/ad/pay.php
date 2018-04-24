<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/pay.php
 * Target : Ads
 * User role : User
 * Description : Pay for an ad that will be added on the server. An admin's ad will be registered immediately.
 * Throttle : 5 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P] message : Message of the ad.
 * [P] interval : Number of times to display the ad per day.
 * [P] expiration : Expiration date of the ad (timestamp).
 * [P][O] duration : Duration of a Title ad.
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

use Delight\Auth;

try {
    $adsky = AdSky::getInstance();
    $auth = $adsky -> getAuth();

    // Throttle protection.
    $auth -> throttle([
        'ad-pay',
        $_SERVER['REMOTE_ADDR']
    ], 5, 60);

    // We check if the user is logged in.
    $user = User::isLoggedIn() -> _object;

    if($user == null) {
        (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
    }

    // We check if the ad is okay.
    $validateResult = Ad::validate(Utils::notEmptyOrNull($_POST, 'type'), Utils::notEmptyOrNull($_POST, 'title'), Utils::notEmptyOrNull($_POST, 'message'), Utils::notEmptyOrNull($_POST, 'interval'), Utils::notEmptyOrNull($_POST, 'expiration'), Utils::notEmptyOrNull($_POST, 'duration'));
    if($validateResult -> _error != null) {
        $validateResult -> returnResponse();
    }

    if(Ad::adExists(Utils::notEmptyOrNull($_POST, 'title'))) {
        (new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'))) -> returnResponse();
    }

    // So now, we are going to create the ad.
    $type = intval($_POST['type']);
    $interval = intval($_POST['interval']);
    $expiration = intval($_POST['expiration']);
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();

    $ad = new Ad($user['username'], $type, $_POST['title'], $_POST['message'], $interval, $expiration, Utils::notEmptyOrNull($_POST, 'duration'));

    // If the user is an admin, we don't have to use the PayPal API.
    if($user['type'] == 0) {
        $response = $ad -> register();
        if($response -> _error != null) {
            $response -> returnResponse();
        }

        $response = new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'), $root . 'admin/?message=create_success#create');
        $response -> returnResponse();
    }

    // Otherwise, let's create a payment !
    $url = $root . 'payment/register/?' . http_build_query($_POST);
    $totalDays = ($expiration - mktime(0, 0, 0)) / (60 * 60 * 24);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $adsky -> getPayPalSettings() -> createApprovalLink($url, $type, $interval, $totalDays));
    $response -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
}
catch(Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(Exception $ex) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'), null, $error);
    $response -> returnResponse();
}