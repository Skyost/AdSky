<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/list.php
 * Target : Ads
 * User role : User
 * Description : List ads from an user. Don't pass "username" parameter to list all ads (you must be an admin).
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P][O] username : Username to list ads.
 * [P][O] page : Current page (to see how many ads there are by page, go to settings/Others.php and check the PAGINATOR_MAX parameter).
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

require_once __DIR__ . '/../../core/Utils.php';

require_once __DIR__ . '/../../core/Response.php';

try {
    $adsky = AdSky::getInstance();

    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    $where = ['ORDER' => 'title'];
    $data = [
        'ad-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ];

    $username = Utils::notEmptyOrNull($_POST, 'username');
    if($username != null) {
        array_push($data, $username);
        $where['username'] = $username;
    }

    AdSky::getInstance() -> getAuth() -> throttle($data, 10, 60);

    $user = $adsky -> getCurrentUserObject();
    if($user == null || ($username != $user -> getUsername() && !$user -> isAdmin())) {
        $response = new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    $mySQLSettings = $adsky -> getMySQLSettings();
    $mySQLSettings -> getPage($mySQLSettings -> getAdsTable(), '*', function($row) {
        return [
            'id' => intval($row['id']),
            'username' => $row['username'],
            'type' => intval($row['type']),
            'title' => $row['title'],
            'message' => $row['message'],
            'interval' => intval($row['interval']),
            'expiration' => intval($row['until']),
            'duration' => intval($row['duration'])
        ];
    }, $page, $where) -> returnResponse();
}
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}