<?php

/**
 * ADSKY API FILE
 *
 * Name : user/forgot_password.php
 * Target : User
 * User role : None
 * Description : Send a password reset request.
 * Throttle : 1 request per hour. 2 open requests max.
 *
 * Parameters :
 * [P] email : Where the password reset request should be sent.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    // We check if an email has been sent.
    if(empty($_POST['email'])) {
        Response::createAndReturn(['API_ERROR_NOT_SET_EMAIL']);
    }

    // If it's okay, we can send the request.
    $adsky -> getAuth() -> forgotPassword($_POST['email'], function($selector, $token) use ($adsky) {
        User::sendEmail($adsky -> getLanguageString('EMAIL_TITLE_RESET'), $_POST['email'], 'reset.twig', [
            'email' => $_POST['email'],
            'selector' => $selector,
            'token' => $token
        ]);
    });

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_ERROR', null, $error);
}
catch(Auth\EmailNotVerifiedException $error) {
    Response::createAndReturn('API_ERROR_NOT_VERIFIED', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Auth\ResetDisabledException $error) {
    Response::createAndReturn('API_ERROR_RESET_DISABLED', null, $error);
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}