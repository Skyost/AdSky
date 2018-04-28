<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$user = $adsky -> getCurrentUserObject();

if($user == null) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
    $response -> returnResponse();
}

$auth = $user -> getAuth();
$username = empty($_POST['username']) ? $auth -> getEmail() : $_POST['username'];
if($username != $user -> getUsername() && !$user -> isAdmin()) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
    $response -> returnResponse();
}

try {
    $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);

    $auth -> admin() -> deleteUserByUsername($username);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\UnknownUsernameException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_USER_NOT_FOUND'), null, $error);
    $response -> returnResponse();
}
catch(Exception $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
    $response -> returnResponse();
}