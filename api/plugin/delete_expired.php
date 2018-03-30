<?php

require '../Lang.php';
require '../Settings.php';

if(empty($_POST['key']) || $_POST['key'] != $settings['PLUGIN_KEY']) {
    (new Response($lang['API_ERROR_INVALID_PLUGIN_KEY'])) -> returnResponse();
}

(Ad :: deleteExpired()) -> returnResponse();