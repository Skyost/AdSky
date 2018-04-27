<?php

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Utils.php';

require_once __DIR__ . '/../../core/Response.php';

try {
    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    $adsky = AdSky::getInstance();

    $adsky -> getAuth() -> throttle([
        'user-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ], 10, 60);

    if(!$adsky -> getAuth() -> hasRole(\Delight\Auth\Role::ADMIN)) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    $mySQLSettings = AdSky::getInstance() -> getMySQLSettings();
    $mySQLSettings -> getPage($mySQLSettings -> getUsersTable(), '*', function($row) {
        return [
            'username' => $row['username'],
            'email' => $row['email'],
            'type' => $row['roles_mask'] & Delight\Auth\Role::ADMIN === Delight\Auth\Role::ADMIN ? User::TYPE_ADMIN : User::TYPE_PUBLISHER,
            'verified' => $row['verified'],
            'last_login' => intval($row['last_login']),
            'registered' => intval($row['registered'])
        ];
    }, $page, ['ORDER' => 'last_login']) -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}