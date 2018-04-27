<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

use Delight\Auth;

$adsky = AdSky::getInstance();
$auth = $adsky -> getAuth();

$user = $adsky -> getCurrentUserObject();

if($user == null) {
    (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
}

try {
    if(isset($_POST['force']) && $_POST['force'] == true) {
        if(!$user -> isAdmin()) {
            (new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'))) -> returnResponse();
        }

        if(isset($_POST['type']) && strlen($_POST['type']) !== 0 && ($_POST['type'] != User::TYPE_ADMIN && $_POST['type'] != User::TYPE_PUBLISHER)) {
            (new Response($adsky -> getLanguageString('API_ERROR_INVALID_TYPE'))) -> returnResponse();
        }

        if(empty($_POST['oldemail'])) {
            $_POST['oldemail'] = $auth -> getEmail();
        }

        $target = new User($auth, $_POST['oldemail'], null, null);

        $currentEmail = $target -> loginAsUserIfNeeded();

        if(!empty($_POST['email'])) {
            $target -> setEmail($_POST['email'], false);
        }

        if(isset($_POST['type'])) {
            $target -> setType(intval($_POST['type']));
        }

        if(!empty($_POST['password'])) {
            $auth -> changePasswordWithoutOldPassword($_POST['password']);
        }

        $auth -> admin() -> logInAsUserByEmail($currentEmail);
        return;
    }

    if(empty($_POST['oldpassword'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]));
        $response -> returnResponse();
    }

    if(!empty($_POST['email'])) {
        $user -> setEmail($_POST['email']);
    }

    if(!empty($_POST['password'])) {
        $auth -> changePassword($_POST['oldpassword'], $_POST['password']);
    }

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(Auth\EmailNotVerifiedException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
    $response -> returnResponse();
}
catch(Auth\InvalidEmailException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(Auth\NotLoggedInException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'), null, $error);
    $response -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Auth\UserAlreadyExistsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_USERNAME_ALREADYEXISTS'), null, $error);
    $response -> returnResponse();
}
catch(Auth\InvalidPasswordException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_PASSWORD'), null, $error);
    $response -> returnResponse();
}