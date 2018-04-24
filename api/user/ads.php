<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

try {
    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    $adsky = AdSky::getInstance();

    $adsky -> getAuth() -> throttle([
        'user-ads',
        $_SERVER['REMOTE_ADDR'],
        $page
    ], 10, 60);

    $language = $adsky -> getLanguage();

    if(empty($_POST['username'])) {
        $response = new Response($language -> formatNotSet($language -> getSettings('API_ERROR_NOT_SET_USERNAME')));
        $response -> returnResponse();
    }

    Ad::getAds($page, $_POST['username']) -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}