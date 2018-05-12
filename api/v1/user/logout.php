<?php

/**
 * ADSKY API FILE
 *
 * Name : user/logout.php
 * Target : User
 * User role : User
 * Description : Logout an user.
 * Throttle : None.
 *
 * Parameters :
 * None.
 */

require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Response.php';

$adsky = AdSky::getInstance();

try {
    // We check if the current user is logged in.
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        throw new \Delight\Auth\AuthError();
    }

    // If yes, let's logout !
    $user -> getAuth() -> logOut();

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}