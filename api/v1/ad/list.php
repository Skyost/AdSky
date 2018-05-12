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
 * [P][O] email : Email to list ads.
 * [P][O] page : Current page (to see how many ads are displayed by page, go to core/settings/WebsiteSettings.php and check the WEBSITE_PAGINATOR_ITEMS_PER_PAGE parameter).
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/Ad.php';

require_once __DIR__ . '/../../../core/Utils.php';

require_once __DIR__ . '/../../../core/Response.php';

$adsky = AdSky::getInstance();

try {
    // We get the required page.
    $page = Utils::notEmptyOrNull($_POST, 'page');
    if($page == null || intval($page) < 1) {
        $page = 1;
    }

    // We check if the user is logged in.
    $email = Utils::notEmptyOrNull($_POST, 'email');
    $user = $adsky -> getCurrentUserObject();
    if($user == null) {
        $response = new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    // Username we want to list ads.
    if($user != null && $email == 'current') {
        $email = $user -> getEmail();
    }

    // Not we check if the user is a admin (or if the username corresponds to the current user).
    if($email != $user -> getEmail() && !$user -> isAdmin()) {
        $response = new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    $where = ['ORDER' => 'title'];
    $data = [
        'ad-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ];

    if($email != null) {
        // We get its username by its email.
        $username = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getUsersTable(), 'username', ['email' => $email]);
        if(empty($username)) {
            throw new Delight\Auth\InvalidEmailException();
        }

        // And then we delete him.
        $username = $username[0];

        if($username != null) {
            array_push($data, $username);
            $where['username'] = $username;
        }
    }

    // Throttle protection.
    $user -> getAuth() -> throttle($data, 10, 60);

    // And let's show everything !
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
catch(\Delight\Auth\InvalidEmailException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}