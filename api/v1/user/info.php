<?php

/**
 * ADSKY API FILE
 *
 * Name : user/info.php
 * Target : User
 * User role : None
 * Description : Gets info about an user. You must be an admin to see other's data.
 * Throttle : None.
 *
 * Parameters :
 * [P] email : User's email. Don't specify it (or just use "current") to see your own data.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky ::getInstance();

    $user = $adsky -> getCurrentUserObject();

    if($user == null) {
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $email = Utils::notEmptyOrNull($_POST, 'email');
    $currentEmail = $user -> getEmail();
    if($email != 'current' && $email != $currentEmail) {
        if(!$user -> isAdmin()) {
            Response::createAndReturn('API_ERROR_NOT_ADMIN');
        }

        $target = new User($adsky -> getAuth(), $email, null, null);
        $target -> loginAsUserIfNeeded();

        $response -> setObject($target -> toArray());
        $user -> loginAsUserIfNeeded();
    }
    else {
        $response -> setObject($user -> toArray());
    }

    $response -> returnResponse();
}
catch(Auth\EmailNotVerifiedException $error) {
    Response::createAndReturn('API_ERROR_NOT_VERIFIED', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Exception $error) {
    Response::createAndReturn('API_ERROR_GENERIC_ERROR', null, $error);
}