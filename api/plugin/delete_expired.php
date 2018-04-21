<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

$adsky = AdSky::getInstance();

if(empty($_POST['key']) || $_POST['key'] != $adsky -> getPluginSettings() -> getPluginKey()) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_PLUGIN_KEY'));
    $response -> returnResponse();
}

(Ad :: deleteExpired()) -> returnResponse();