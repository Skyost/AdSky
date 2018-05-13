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
 * [O] oldemail : "Old" account's email.
 * [O] oldpassword : "Old" account's password.
 * [P][O] email : New account's email.
 * [P][O] password : New account's password.
 * [P][O] force : Force update the account (allows to not enter the the old password and to change the type). If set to true, you must specify the "oldemail" parameter to identify the target account. Only admins can do that.
 * [P][O] type : New account's type.
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

try {
    Autoloader::register();
    $adsky = AdSky::getInstance();

    $auth = $adsky -> getAuth();
    $user = $adsky -> getCurrentUserObject();

    // We check if the current user is logged in.
    if($user == null) {
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // We also have to check if the force parameter is set to true.
    if(isset($_POST['force']) && $_POST['force'] == true) {
        // The user must be an admin to use the force mode.
        if(!$user -> isAdmin()) {
            Response::createAndReturn('API_ERROR_NOT_ADMIN');
        }

        // We check if a valid type has been sent.
        if(!Utils::trueEmpty($_POST, 'type') && ($_POST['type'] != User::TYPE_ADMIN && $_POST['type'] != User::TYPE_PUBLISHER)) {
            Response::createAndReturn('API_ERROR_INVALID_TYPE');
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
        Response::createAndReturn(null, 'API_SUCCESS');
        return;
    }

    // If we are not in force mode, we must have an old password.
    if(empty($_POST['oldpassword'])) {
        Response::createAndReturn(['API_ERROR_NOT_SET_OLDPASSWORD']);
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

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}
catch(Auth\EmailNotVerifiedException $error) {
    Response::createAndReturn('API_ERROR_NOT_VERIFIED', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Auth\NotLoggedInException $error) {
    Response::createAndReturn('API_ERROR_NOT_LOGGEDIN', null, $error);
}
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Auth\UserAlreadyExistsException $error) {
    Response::createAndReturn('API_ERROR_EMAIL_ALREADYEXISTS', null, $error);
}
catch(Auth\InvalidPasswordException $error) {
    Response::createAndReturn('API_ERROR_INVALID_PASSWORD', null, $error);
}