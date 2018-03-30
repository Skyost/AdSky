<?php

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

if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != 0 && $_POST['type'] != 1)) {
    (new Response($lang['API_ERROR_INVALID_TYPE'])) -> returnResponse();
}

require_once '../objects/Ad.php';

((new Ad($_POST['username'], $_POST['oldtype'], $_POST['oldtitle'])) -> update(utilNotEmptyOrNull($_POST, 'type'), utilNotEmptyOrNull($_POST, 'title'), utilNotEmptyOrNull($_POST, 'message'), utilNotEmptyOrNull($_POST, 'interval'), utilNotEmptyOrNull($_POST, 'expiration'), utilNotEmptyOrNull($_POST, 'duration'), $pdo)) -> returnResponse();