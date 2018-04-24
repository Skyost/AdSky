<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/list.php
 * Target : Ads
 * User role : Admin
 * Description : List all ads.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P][O] page : Current page (to see how many ads there are by page, go to settings/Others.php and check the PAGINATOR_MAX parameter).
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

try {
    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    AdSky::getInstance() -> getAuth() -> throttle([
        'ad-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ], 10, 60);

    $object = User::isLoggedIn() -> _object;
    if($object == null || $object['type'] !== 0) {
        $response = new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    Ad::getAds($page, null) -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}