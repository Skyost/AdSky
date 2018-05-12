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

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Utils.php';

require_once __DIR__ . '/../../../core/Response.php';

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

try {
    // We check if an email has been sent.
    if(empty($_POST['email'])) {
        $response = new Response(null, $language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_EMAIL')]));
        $response -> returnResponse();
    }

    // If it's okay, we can send the request.
    $adsky -> getAuth() -> forgotPassword($_POST['email'], function($selector, $token) use ($adsky) {
        User::sendEmail($adsky -> getLanguageString('EMAIL_TITLE_RESET'), $_POST['email'], 'reset.twig', [
            'email' => $_POST['email'],
            'selector' => $selector,
            'token' => $token
        ]);
    });

    $response = new Response(null, $language -> getSettings('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($language -> getSettings('API_ERROR_GENERIC_ERROR'), null, $error);
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
catch(\Delight\Auth\ResetDisabledException $error) {
    $response = new Response($language -> getSettings('API_ERROR_RESET_DISABLED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}