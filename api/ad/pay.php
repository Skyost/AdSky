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

require_once __DIR__ . '/../../core/Response.php';

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
    $user = $adsky -> getCurrentUserObject();

    if($user == null) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    // We check if the ad is okay.
    if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != Ad::TYPE_TITLE && $_POST['type'] != Ad::TYPE_CHAT)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_TYPE'));
        $response -> returnResponse();
    }

    $type = intval($_POST['type']);
    $adSettings = $adsky -> getAdSettings();

    if(!$adSettings -> validateTitle(Utils::notEmptyOrNull($_POST, 'title'), $type)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_TITLE'));
        $response -> returnResponse();
    }

    if(!$adSettings -> validateMessage(Utils::notEmptyOrNull($_POST, 'message'), $type)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_MESSAGE'));
        $response -> returnResponse();
    }

    if(!$adSettings -> validateInterval(Utils::notEmptyOrNull($_POST, 'interval'), $type)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_INTERVAL'));
        $response -> returnResponse();
    }

    if(!$adSettings -> validateExpiration(Utils::notEmptyOrNull($_POST, 'expiration'), $type)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EXPIRATIONDATE'));
        $response -> returnResponse();
    }

    if($type == Ad::TYPE_TITLE && !$adSettings -> validateDuration(Utils::notEmptyOrNull($_POST, 'duration'))) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_DURATION'));
        $response -> returnResponse();
    }

    if(Ad::titleExists($_POST['title'])) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
        $response -> returnResponse();
    }

    // So now, we are going to create the ad.
    $interval = intval($_POST['interval']);
    $expiration = intval($_POST['expiration']);
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();

    // If the user is an admin, we don't have to use the PayPal API.
    if($user -> isAdmin()) {
        $ad = new Ad($user -> getUsername(), $type, $_POST['title'], $_POST['message'], $interval, $expiration, Utils::notEmptyOrNull($_POST, 'duration'));
        $ad -> sendUpdateToDatabase();

        $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $root . 'admin/?message=create_success#create');
        $response -> returnResponse();
    }

    // Otherwise, let's create a payment !
    $url = $root . 'payment/register/?' . http_build_query($_POST);
    $totalDays = ($expiration - gmmktime(0, 0, 0)) / (60 * 60 * 24);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $adsky -> getPayPalSettings() -> createApprovalLink($url, $type, $interval, $totalDays));
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
catch(Exception $ex) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'), null, $error);
    $response -> returnResponse();
}