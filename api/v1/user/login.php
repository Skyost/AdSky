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
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Utils.php';

require_once __DIR__ . '/../../../core/Response.php';

$adsky = AdSky::getInstance();
$language = AdSky::getInstance() -> getLanguage();

try {
    // We check if an email and a password have been sent.
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_EMAIL'), $language -> getSettings('API_ERROR_NOT_SET_PASSWORD')]));
        $response -> returnResponse();
    }

    // If yes, we check if the user is already logged in.
    if($adsky -> getCurrentUserObject() != null) {
        throw new \Delight\Auth\AuthError();
    }

    // Else, we try to login him.
    $adsky -> getAuth() -> login($_POST['email'], $_POST['password'], Utils::notEmptyOrNull($_POST, 'rememberduration'));

    $response = new Response(null, $language -> getSettings('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AttemptCancelledException $error) {
    $response = new Response($language -> getSettings('API_ERROR_ATTEMPT_CANCELLED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($language -> getSettings('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\EmailNotVerifiedException $error) {
    $response = new Response($language -> getSettings('API_ERROR_NOT_VERIFIED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidEmailException $error) {
    $response = new Response($language -> getSettings('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidPasswordException $error) {
    $response = new Response($language -> getSettings('API_ERROR_INVALID_PASSWORD'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}