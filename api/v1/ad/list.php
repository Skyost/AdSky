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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Response;
use AdSky\Core\Utils;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
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
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // Username we want to list ads.
    if($user != null && $email == 'current') {
        $email = $user -> getEmail();
    }

    // Not we check if the user is a admin (or if the username corresponds to the current user).
    if($email != $user -> getEmail() && !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
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
            throw new Auth\InvalidEmailException();
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
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}