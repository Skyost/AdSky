<?php

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