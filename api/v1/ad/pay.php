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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
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
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // We check if the ad is okay.
    if(!Utils::trueEmpty($_POST, 'type') && ($_POST['type'] != Ad::TYPE_TITLE && $_POST['type'] != Ad::TYPE_CHAT)) {
        Response::createAndReturn('API_ERROR_INVALID_TYPE');
    }

    $type = intval(Utils::notEmptyOrNull($_POST, 'type'));
    $message = Utils::notEmptyOrNull($_POST, 'message');

    $adSettings = $adsky -> getAdSettings();

    if(!$adSettings -> validateTitle(Utils::notEmptyOrNull($_POST, 'title'), $type)) {
        Response::createAndReturn('API_ERROR_INVALID_TITLE');
    }

    if(!$adSettings -> validateMessage($message, $type)) {
        Response::createAndReturn('API_ERROR_INVALID_MESSAGE');
    }

    if(!$adSettings -> validateInterval(Utils::notEmptyOrNull($_POST, 'interval'), $type)) {
        Response::createAndReturn('API_ERROR_INVALID_INTERVAL');
    }

    if(!$adSettings -> validateExpiration(Utils::notEmptyOrNull($_POST, 'expiration'), $type)) {
        Response::createAndReturn('API_ERROR_INVALID_EXPIRATIONDATE');
    }

    if($type == Ad::TYPE_TITLE && !$adSettings -> validateDuration(Utils::notEmptyOrNull($_POST, 'duration'))) {
        Response::createAndReturn('API_ERROR_INVALID_DURATION');
    }

    if(Ad::titleExists($_POST['title'])) {
        Response::createAndReturn('API_ERROR_SAME_NAME');
    }

    // So now, we are going to create the ad.
    $interval = intval($_POST['interval']);
    $expiration = intval($_POST['expiration']);
    $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();

	$numberOfAdsPerDay = $adsky -> getMedoo() -> sum($adsky -> getMySQLSettings() -> getAdsTable(), 'interval', []);
    if($adSettings -> getAdPerDayLimit() > 0 && $numberOfAdsPerDay + $interval > $adSettings -> getAdPerDayLimit()) {
        Response::createAndReturn('API_ERROR_LIMIT_REACHED');
    }

    // If the user is an admin, we don't have to use the PayPal API.
    if($user -> isAdmin()) {
        $ad = new Ad($user -> getUsername(), $type, $_POST['title'], $message, $interval, $expiration, Utils::notEmptyOrNull($_POST, 'duration'));
        $ad -> sendUpdateToDatabase();

        Response::createAndReturn(null, 'API_SUCCESS', $root . 'admin/?message=create_success#create');
    }

    // Otherwise, let's create a payment !
    $url = $root . 'payment/register/?' . http_build_query($_POST);
    $totalDays = ($expiration - gmmktime(0, 0, 0)) / (60 * 60 * 24);

    Response::createAndReturn(null, 'API_SUCCESS', $adsky -> getPayPalSettings() -> createApprovalLink($url, $type, $interval, $totalDays));
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
catch(Exception $ex) {
    Response::createAndReturn('API_ERROR_PAYPAL_REQUEST', null, $error);
}