<?php

require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();
$user = $adsky -> getCurrentUserObject();

if($user == null) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
    $response -> returnResponse();
}

$response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $user -> toArray());
$response -> returnResponse();