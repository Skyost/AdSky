<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

if(empty($_POST['username'])) {
    $response = new Response($language -> formatNotSet($language -> getSettings('API_ERROR_NOT_SET_USERNAME')));
    $response -> returnResponse();
}

$response = Ad :: getAds(Utils::notEmptyOrNull($_POST, 'page'), $_POST['username']);
$response -> returnResponse();