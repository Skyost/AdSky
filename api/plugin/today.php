<?php

require_once '../Lang.php';
require_once '../Settings.php';
require_once '../objects/Ad.php';
require_once '../objects/Response.php';

if(empty($_POST['key']) || $_POST['key'] != $settings['PLUGIN_KEY']) {
    (new Response($lang['API_ERROR_INVALID_PLUGIN_KEY'])) -> returnResponse();
}

(Ad :: todayAds()) -> returnResponse();