<?php

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

if(empty($_POST['username'])) {
    $_POST['username'] = $object['username'];
}
else {
    if($object['username'] != $_POST['username'] && $object['type'] !== 0) {
        (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
    }
}

require_once '../objects/Ad.php';

((new Ad($_POST['username'], intval($_POST['type']), $_POST['title'])) -> delete($pdo)) -> returnResponse();