<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

try {
    if(empty($_POST['email'])) {
        $response = new Response(null, $language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_EMAIL')]));
        $response -> returnResponse();
    }
    
    $adsky -> getAuth() -> forgotPassword($_POST['email'], function($selector, $token) {
        User::sendEmail('Password reset', $_POST['email'], 'reset.twig', [
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