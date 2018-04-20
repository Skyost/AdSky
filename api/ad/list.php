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

require '../Lang.php';

require '../objects/User.php';

$pdo = getPDO();
$auth = createAuth($pdo);

$object = User::isLoggedIn($auth) -> _object;
if($object == null || $object['type'] !== 0) {
    (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
}

require '../objects/Ad.php';

(Ad :: getAds(utilNotEmptyOrNull($_POST, 'page'), null, $pdo)) -> returnResponse();