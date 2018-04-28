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

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$language = AdSky::getInstance() -> getLanguage();

try {
    // We check if all required parameters have been sent.
    if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_USERNAME'), $language -> getSettings('API_ERROR_NOT_SET_EMAIL'), $language -> getSettings('API_ERROR_NOT_SET_PASSWORD')]));
        $response -> returnResponse();
    }

    // We also check if the user is not logged in.
    if($adsky -> getCurrentUserObject() != null) {
        throw new \Delight\Auth\AuthError();
    }

    // Now that we are sure, we register the user.
    $user = User::register($_POST['username'], $_POST['email'], $_POST['password']);

    $response = new Response(null, $language -> getSettings('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($language -> getSettings('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\DuplicateUsernameException $error) {
    $response = new Response($language -> getSettings('API_ERROR_USERNAME_ALREADYEXISTS'), null, $error);
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
catch(\Delight\Auth\UnknownIdException $error) {
    $response = new Response($language -> getSettings('API_ERROR_UNKNOWN_ID'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\UserAlreadyExistsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_EMAIL_ALREADYEXISTS'), null, $error);
    $response -> returnResponse();
}