<?php

require '../Lang.php';
require '../objects/User.php';

if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
    (new Response(formatNotSet([$lang['API_ERROR_NOT_SET_USERNAME'], $lang['API_ERROR_NOT_SET_EMAIL'], $lang['API_ERROR_NOT_SET_PASSWORD']]))) -> returnResponse();
}

$user = new User($_POST['email'], $_POST['password'], $_POST['username'], $settings['APP_DEBUG'] ? \Delight\Auth\Role::ADMIN : null);
($user -> register()) -> returnResponse();