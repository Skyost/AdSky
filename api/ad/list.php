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

$object = User::isLoggedIn() -> _object;
if($object == null || $object['type'] !== 0) {
    $response = new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_NOT_ADMIN'));
    $response -> returnResponse();
}

$response = Ad :: getAds(Utils::notEmptyOrNull($_POST, 'page'), null);
$response -> returnResponse();