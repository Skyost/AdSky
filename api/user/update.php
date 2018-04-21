<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

use Delight\Auth;

$adsky = AdSky::getInstance();
$auth = $adsky -> getAuth();
$language = $adsky -> getLanguage();

$object = User::isLoggedIn() -> _object;

if($object == null) {
    (new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
}

if($object['type'] === 0) {
    if(empty($_POST['oldpassword']) && !isset($_POST['adminmode'])) {
        (new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]))) -> returnResponse();
    }

    if(empty($_POST['oldemail'])) {
        $_POST['oldemail'] = $auth -> getEmail();
    }

    if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != 0 && $_POST['type'] != 1)) {
        (new Response($language -> getSettings('API_ERROR_INVALID_TYPE'))) -> returnResponse();
    }

    $email = empty($_POST['email']) ? $auth -> getEmail() : $_POST['email'];
    $type = null;

    if(isset($_POST['type']) && $_POST['type'] == 0) {
        $type = Auth\Role::ADMIN;
    }

    $target = new User($_POST['oldemail'], Utils::notEmptyOrNull($_POST, 'oldpassword'), null, null);
    $response = $target -> update($email, Utils::notEmptyOrNull($_POST, 'password'), $type);

    if($response -> _error == null && $_POST['oldemail'] != $email) {
        try {
            $auth -> logOut();
            $auth -> destroySession();
        }
        catch(Auth\AuthError $error) {
            (new Response($language -> getSettings('API_ERROR_GENERIC_AUTH_ERROR'))) -> returnResponse();
        }
    }

    $response -> returnResponse();
}

if(empty($_POST['oldpassword'])) {
    (new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]))) -> returnResponse();
}

$target = new User($auth -> getEmail(), $_POST['oldpassword'], $auth -> getUsername(), $auth -> hasRole(Auth\Role::ADMIN) ? Auth\Role::ADMIN : Auth\Role::PUBLISHER);
$response = $target -> update(Utils::notEmptyOrNull($_POST, 'email'), Utils::notEmptyOrNull($_POST, 'password'));
$response -> returnResponse();