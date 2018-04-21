<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

$adsky = AdSky::getInstance();
$auth = $adsky -> getAuth();

$username = empty($_POST['username']) ? $auth -> getEmail() : $_POST['username'];
if($username != $auth -> getUsername() && !$auth -> hasRole(\Delight\Auth\Role::ADMIN)) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
    $response -> returnResponse();
}

$response = (new User(null, null, $username)) -> delete();
$response -> returnResponse();