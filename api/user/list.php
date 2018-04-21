<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

$adsky = AdSky::getInstance();

if(!$adsky -> getAuth() -> hasRole(\Delight\Auth\Role::ADMIN)) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
    $response -> returnResponse();
}

User::getUsers(Utils::notEmptyOrNull($_POST, 'page')) -> returnResponse();