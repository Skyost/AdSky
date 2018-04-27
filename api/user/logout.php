<?php

require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

try {
    $adsky = AdSky::getInstance();
    $adsky -> getAuth() -> logOut();

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(\Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}