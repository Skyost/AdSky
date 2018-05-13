<?php

/**
 * ADSKY API FILE
 *
 * Name : user/register.php
 * Target : User
 * User role : None
 * Description : Register an user.
 * Throttle : 1 request per 12 hours.
 *
 * Parameters :
 * [P] username : Account's username.
 * [P] email : Account's email.
 * [P] password : Account's password.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
$adsky = AdSky::getInstance();

try {
    // We check if all required parameters have been sent.
    if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        Response::createAndReturn('API_ERROR_NOT_SET_USERNAME', 'API_ERROR_NOT_SET_EMAIL', 'API_ERROR_NOT_SET_PASSWORD');
    }

    // We also check if the user is not logged in.
    if($adsky -> getCurrentUserObject() != null) {
        throw new Auth\AuthError();
    }

    // Now that we are sure, we register the user.
    $user = User::register($_POST['username'], $_POST['email'], $_POST['password']);

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}
catch(Auth\DuplicateUsernameException $error) {
    Response::createAndReturn('API_ERROR_USERNAME_ALREADYEXISTS', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Auth\InvalidPasswordException $error) {
    Response::createAndReturn('API_ERROR_INVALID_PASSWORD', null, $error);
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Auth\UnknownIdException $error) {
    Response::createAndReturn('API_ERROR_UNKNOWN_ID', null, $error);
}
catch(Auth\UserAlreadyExistsException $error) {
    Response::createAndReturn('API_ERROR_EMAIL_ALREADYEXISTS', null, $error);
}