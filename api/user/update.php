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
    if(empty($_GET['oldpassword']) && !isset($_GET['adminmode'])) {
        (new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]))) -> returnResponse();
    }

    if(empty($_GET['oldemail'])) {
        $_GET['oldemail'] = $auth -> getEmail();
    }

    if(isset($_GET['type']) && strlen($_GET['type']) !== 0 && ($_GET['type'] != 0 && $_GET['type'] != 1)) {
        (new Response($language -> getSettings('API_ERROR_INVALID_TYPE'))) -> returnResponse();
    }

    $email = empty($_GET['email']) ? $auth -> getEmail() : $_GET['email'];
    $type = isset($_GET['type']) && $_GET['type'] == 0 ? Auth\Role::ADMIN : null;

    $target = new User($_GET['oldemail'], Utils::notEmptyOrNull($_GET, 'oldpassword'));
    $response = $target -> update($email, Utils::notEmptyOrNull($_GET, 'password'), $type);

    if($response -> _error == null && $_GET['oldemail'] != $email) {
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

if(empty($_GET['oldpassword'])) {
    (new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]))) -> returnResponse();
}

$target = new User($auth -> getEmail(), $_GET['oldpassword'], $auth -> getUsername(), $auth -> hasRole(Auth\Role::ADMIN) ? Auth\Role::ADMIN : Auth\Role::PUBLISHER);
$response = $target -> update(Utils::notEmptyOrNull($_GET, 'email'), Utils::notEmptyOrNull($_GET, 'password'));
$response -> returnResponse();