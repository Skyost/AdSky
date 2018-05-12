<?php

/**
 * ADSKY API FILE
 *
 * Name : user/info.php
 * Target : User
 * User role : None
 * Description : Gets info about an user. You must be an admin to see other's data.
 * Throttle : None.
 *
 * Parameters :
 * [P] email : User's email. Don't specify it (or just use "current") to see your own data.
 */

require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Utils.php';

try {
    $adsky = AdSky ::getInstance();
    $user = $adsky -> getCurrentUserObject();

    if($user == null) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    $email = Utils::notEmptyOrNull($_POST, 'email');
    $currentEmail = $user -> getEmail();
    if($email != 'current' && $email != $currentEmail) {
        if(!$user -> isAdmin()) {
            $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
            $response -> returnResponse();
        }

        $target = new User($adsky -> getAuth(), $email, null, null);
        $target -> loginAsUserIfNeeded();

        $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $target -> toArray());

        $user -> loginAsUserIfNeeded();
    }
    else {
        $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $user -> toArray());
    }

    $response -> returnResponse();
}
catch(\Delight\Auth\EmailNotVerifiedException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidEmailException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(Exception $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
    $response -> returnResponse();
}