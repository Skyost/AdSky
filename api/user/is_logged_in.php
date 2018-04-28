<?php

/**
 * ADSKY API FILE
 *
 * Name : user/is_logged_in.php
 * Target : User
 * User role : None
 * Description : Checks if an user is logged in.
 * Throttle : None.
 *
 * Parameters :
 * None.
 */

require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$user = $adsky -> getCurrentUserObject();

// We check if the current user is logged in.
if($user == null) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
    $response -> returnResponse();
}

// If yes, we send the response.
$response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $user -> toArray());
$response -> returnResponse();