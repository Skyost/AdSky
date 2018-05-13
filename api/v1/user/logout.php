<?php

/**
 * ADSKY API FILE
 *
 * Name : user/logout.php
 * Target : User
 * User role : User
 * Description : Logout an user.
 * Throttle : None.
 *
 * Parameters :
 * None.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // We check if the current user is logged in.
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        throw new \Delight\Auth\AuthError();
    }

    // If yes, let's logout !
    $user -> getAuth() -> logOut();
    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}