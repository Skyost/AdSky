<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

require_once __DIR__ . '/../../core/Response.php';

if(empty($_POST['email']) || empty($_POST['password'])) {
    $language = AdSky::getInstance() -> getLanguage();

    $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_EMAIL'), $language -> getSettings('API_ERROR_NOT_SET_PASSWORD')]));
    $response -> returnResponse();
}

$adsky = AdSky::getInstance();

try {
    $adsky -> getAuth() -> login($_POST['email'], $_POST['password'], Utils::notEmptyOrNull($_POST, 'rememberduration'));

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AttemptCancelledException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_ATTEMPT_CANCELLED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\EmailNotVerifiedException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidEmailException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidPasswordException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_PASSWORD'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}