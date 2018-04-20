<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/delete.php
 * Target : Ads
 * User role : User
 * Description : Delete an ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P][O] username : Username of the owner of the ad. If not sent, it will be set to the current user's username. An admin can delete anyone's ad.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require '../Lang.php';

require '../objects/User.php';
require_once '../objects/Response.php';
require_once '../Settings.php';

if(!isset($_POST['type']) || strlen($_POST['type']) === 0 || empty($_POST['title'])) {
    (new Response(formatNotSet([$lang['API_ERROR_NOT_SET_TITLE'], $lang['API_ERROR_NOT_SET_TYPE']]))) -> returnResponse();
}

$pdo = getPDO();
$auth = createAuth($pdo);

$object = User::isLoggedIn($auth) -> _object;
if($object == null) {
    (new Response($lang['API_ERROR_NOT_LOGGEDIN'])) -> returnResponse();
}

require_once '../objects/Ad.php';

if(empty($_POST['username'])) {
    $_POST['username'] = $object['username'];
}
else {
    if($object['username'] != $_POST['username'] && $object['type'] !== Ad::TYPE_TITLE) {
        (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
    }
}

((new Ad($_POST['username'], intval($_POST['type']), $_POST['title'])) -> delete($pdo)) -> returnResponse();