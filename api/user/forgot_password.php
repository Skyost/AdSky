<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

$adsky = AdSky::getInstance();

$response = (new User(Utils::notEmptyOrNull($_POST, 'email'))) -> forgotPassword();
$response -> returnResponse();