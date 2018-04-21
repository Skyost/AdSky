<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
    $language = AdSky::getInstance() -> getLanguage();
    $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_USERNAME'), $language -> getSettings('API_ERROR_NOT_SET_EMAIL'), $language -> getSettings('API_ERROR_NOT_SET_PASSWORD')]));
    $response -> returnResponse();
}

$user = new User($_POST['email'], $_POST['password'], $_POST['username'], AdSky::APP_DEBUG ? \Delight\Auth\Role::ADMIN : null);
$response = $user -> register();
$response -> returnResponse();