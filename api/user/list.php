<?php

require '../Lang.php';
require '../objects/User.php';

$auth = createAuth();

if(!$auth -> hasRole(\Delight\Auth\Role::ADMIN)) {
    (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
}

User::getUsers(utilNotEmptyOrNull($_POST, 'page')) -> returnResponse();