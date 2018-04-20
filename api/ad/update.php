<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/update.php
 * Target : Ads
 * User role : Admin
 * Description : Update an ad. An admin can update anyone's ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] oldtype : Current type of the ad (Title / Chat).
 * [P] oldtitle : Current title of the ad.
 * [P] username : Owner of the ad.
 * [P][O] type : New type of the ad (Title / Chat).
 * [P][O] title : New title of the ad.
 * [P][O] message : New message of the ad.
 * [P][O] interval : New interval (times to display per day) of the ad.
 * [P][O] expiration : New expiration of the ad (in timestamp).
 * [P][O] duration : New duration of the ad (for Title ads).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

require_once '../objects/User.php';
require_once '../objects/Response.php';
require_once '../Settings.php';

$pdo = getPDO();
$auth = createAuth($pdo);
$object = User::isLoggedIn($auth) -> _object;

if($object == null || $object['type'] != 0) {
    (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
}

if((!isset($_POST['oldtype']) || strlen($_POST['oldtype']) === 0) || empty($_POST['oldtitle']) || empty($_POST['username'])) {
    (new Response(formatNotSet([$lang['API_ERROR_NOT_SET_OLDTITLE'], $lang['API_ERROR_NOT_SET_OLDTYPE'], $lang['API_ERROR_NOT_SET_USERNAME']]))) -> returnResponse();
}

if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != Ad::TYPE_TITLE && $_POST['type'] != Ad::TYPE_CHAT)) {
    (new Response($lang['API_ERROR_INVALID_TYPE'])) -> returnResponse();
}

require_once '../objects/Ad.php';

((new Ad($_POST['username'], $_POST['oldtype'], $_POST['oldtitle'])) -> update(utilNotEmptyOrNull($_POST, 'type'), utilNotEmptyOrNull($_POST, 'title'), utilNotEmptyOrNull($_POST, 'message'), utilNotEmptyOrNull($_POST, 'interval'), utilNotEmptyOrNull($_POST, 'expiration'), utilNotEmptyOrNull($_POST, 'duration'), $pdo)) -> returnResponse();