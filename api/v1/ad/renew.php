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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
$adsky = AdSky::getInstance();

try {
    // We check if all arguments are okay.
    if(Utils::trueEmpty($_POST, 'id') || empty($_POST['days'])) {
        Response::createAndReturn(['API_ERROR_NOT_SET_ID', 'API_ERROR_NOT_SET_DAYS']);
    }

    $days = intval($_POST['days']);
    if($days <= 0) {
        Response::createAndReturn('API_ERROR_INVALID_RENEWDAY');
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
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // Okay, now we can select our ad.
    $ad = Ad::getFromDatabase(intval($_POST['id']));
    if($ad == null) {
        Response::createAndReturn('API_ERROR_AD_NOT_FOUND');
    }

    // We check if the days parameter is good.
    if(!$ad -> renew($days)) {
        Response::createAndReturn('API_ERROR_INVALID_RENEWDAY');
    }

    // If the user is an admin, we don't have to use the PayPal API.
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();
    if($user -> isAdmin()) {
        $ad -> sendUpdateToDatabase($_POST['id']);
        Response::createAndReturn(null, 'API_SUCCESS', $root . 'admin/?message=renew_success#list');
    }

    // Otherwise, let's create a payment !
    $url = $root . 'payment/renew/?' . http_build_query($_POST);
    Response::createAndReturn(null, 'API_SUCCESS', $adsky -> getPayPalSettings() -> createApprovalLink($url, $ad -> getType(), $ad -> getInterval(), $days));
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}
catch(PDOException $error) {
    Response::createAndReturn('API_ERROR_MYSQL_ERROR', null, $error);
}
catch(Exception $error) {
    Response::createAndReturn('API_ERROR_PAYPAL_REQUEST', null, $error);
}