<?php

/**
 * ADSKY API FILE
 *
 * Name : update/update.php
 * Target : AdSky
 * User role : Admin
 * Description : Allows to update AdSky (if available).
 * Throttle : 1 request per hour.
 *
 * Parameters :
 * None.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\GithubUpdater;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
$adsky = AdSky::getInstance();

try {
    // We check if the current user is an admin.
    $user = $adsky -> getCurrentUserObject();
    if($user == null || !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // Throttle protection.
    $auth = $adsky -> getAuth();
    $auth -> throttle([
        'update-update',
        $_SERVER['REMOTE_ADDR']
    ], 1, 3600);

    // Then we update.
    $updater = new GithubUpdater();
    if(!$updater -> update()) {
        Response::createAndReturn('API_UPDATE_ERROR');
    }

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Exception $error) {
    Response::createAndReturn('API_UPDATE_ERROR', null, $error);
}