<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

try {
    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    $adsky = AdSky::getInstance();

    $adsky -> getAuth() -> throttle([
        'user-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ], 10, 60);

    if(!$adsky -> getAuth() -> hasRole(\Delight\Auth\Role::ADMIN)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    User::getUsers($page) -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}