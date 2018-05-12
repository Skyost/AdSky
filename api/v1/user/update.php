<?php

/**
 * ADSKY API FILE
 *
 * Name : user/update.php
 * Target : User
 * User role : User
 * Description : Edit an user account.
 * Throttle : None.
 *
 * Parameters :
 * [P][O] username : New account's username.
 * [P][O] email : New account's email.
 * [P][O] password : New account's password.
 * [P][O] force : Force update the account (allows to not enter the the old password and to change the type). If set to true, you must specify the "oldemail" parameter to identify the target account. Only admins can do that.
 * [P][O] type : New account's type.
 * [P][O] oldemail : "Old" account's email.
 * [P][O] oldpassword : "Old" account's password.
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Response.php';

require_once __DIR__ . '/../../../core/Utils.php';

use Delight\Auth;

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

try {
    $auth = $adsky -> getAuth();
    $user = $adsky -> getCurrentUserObject();

    // We check if the current user is logged in.
    if($user == null) {
        (new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
    }

    // We also have to check if the force parameter is set to true.
    if(isset($_POST['force']) && $_POST['force'] == true) {
        // The user must be an admin to use the force mode.
        if(!$user -> isAdmin()) {
            $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
            $response -> returnResponse();
        }

        // We check if a valid type has been sent.
        if(!Utils::trueEmpty($_POST, 'type') && ($_POST['type'] != User::TYPE_ADMIN && $_POST['type'] != User::TYPE_PUBLISHER)) {
            $response = new Response($language -> getSettings('API_ERROR_INVALID_TYPE'));
            $response -> returnResponse();
        }

        // We prepare our target.
        $target = new User($auth, empty($_POST['oldemail']) || $_POST['oldemail'] == 'current' ? $auth -> getEmail() : $_POST['oldemail'], null, null);

        // We impersonate the user if needed.
        $target -> loginAsUserIfNeeded();

        // Now we can edit everything.
        if(!empty($_POST['email'])) {
            $target -> setEmail($_POST['email'], false);
        }

        if(isset($_POST['type'])) {
            $target -> setType(intval($_POST['type']));
        }

        if(!empty($_POST['password'])) {
            $auth -> changePasswordWithoutOldPassword($_POST['password']);
        }

        // We login back our admin.
        $user -> loginAsUserIfNeeded();

        $response = new Response(null, $language -> getSettings('API_SUCCESS'));
        $response -> returnResponse();
        return;
    }

    // If we are not in force mode, we must have an old password.
    if(empty($_POST['oldpassword'])) {
        $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_OLDPASSWORD')]));
        $response -> returnResponse();
    }

    // We check if the old password is okay.
    if(!$auth -> reconfirmPassword($_POST['oldpassword'])) {
        throw new Auth\InvalidPasswordException();
    }

    // If yes, we can edit everything.
    if(!empty($_POST['email'])) {
        $user -> setEmail($_POST['email']);
    }

    if(!empty($_POST['password'])) {
        $auth -> changePassword($_POST['oldpassword'], $_POST['password']);
    }

    $response = new Response(null, $language -> getSettings('API_SUCCESS'));
    $response -> returnResponse();
}
catch(Auth\AuthError $error) {
    $response = new Response($language -> getSettings('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(Auth\EmailNotVerifiedException $error) {
    $response = new Response($language -> getSettings('API_ERROR_NOT_VERIFIED'), null, $error);
    $response -> returnResponse();
}
catch(Auth\InvalidEmailException $error) {
    $response = new Response($language -> getSettings('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(Auth\NotLoggedInException $error) {
    $response = new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'), null, $error);
    $response -> returnResponse();
}
catch(Auth\TooManyRequestsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Auth\UserAlreadyExistsException $error) {
    $response = new Response($language -> getSettings('API_ERROR_EMAIL_ALREADYEXISTS'), null, $error);
    $response -> returnResponse();
}
catch(Auth\InvalidPasswordException $error) {
    $response = new Response($language -> getSettings('API_ERROR_INVALID_PASSWORD'), null, $error);
    $response -> returnResponse();
}