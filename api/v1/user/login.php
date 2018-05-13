<?php

/**
 * ADSKY API FILE
 *
 * Name : user/login.php
 * Target : User
 * User role : None
 * Description : Login an user.
 * Throttle : 500 requests per day.
 *
 * Parameters :
 * [P] email : Account's email.
 * [P] password : Account's password.
 * [P][O] rememberduration : Remember duration in seconds.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // We check if an email and a password have been sent.
    if(empty($_POST['email']) || empty($_POST['password'])) {
        Response::createAndReturn(['API_ERROR_NOT_SET_EMAIL', 'API_ERROR_NOT_SET_PASSWORD']);
    }

    // If yes, we check if the user is already logged in.
    if($adsky -> getCurrentUserObject() != null) {
        throw new Auth\AuthError();
    }

    // Else, we try to login him.
    $adsky -> getAuth() -> login($_POST['email'], $_POST['password'], Utils::notEmptyOrNull($_POST, 'rememberduration'));

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\AttemptCancelledException $error) {
    Response::createAndReturn('API_ERROR_ATTEMPT_CANCELLED', null, $error);
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}
catch(Auth\EmailNotVerifiedException $error) {
    Response::createAndReturn('API_ERROR_NOT_VERIFIED', null, $error);
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