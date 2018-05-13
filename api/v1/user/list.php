<?php

/**
 * ADSKY API FILE
 *
 * Name : user/list.php
 * Target : User
 * User role : Admin
 * Description : List all registered users.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P][O] page : Current page (to see how many users are displayed by page, go to core/settings/WebsiteSettings.php and check the WEBSITE_PAGINATOR_ITEMS_PER_PAGE parameter).
 */

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
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

    $user = $adsky -> getCurrentUserObject();

    // We check if the current user is an admin.
    if($user == null || !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // Throttle protection.
    $user -> getAuth() -> throttle([
        'user-list',
        $_SERVER['REMOTE_ADDR'],
        $page
    ], 10, 60);

    // And let's show everything !
    $mySQLSettings = $adsky -> getMySQLSettings();
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
catch(Auth\TooManyRequestsException $error) {
    Response::createAndReturn('API_ERROR_TOOMANYREQUESTS', null, $error);
}
catch(Auth\AuthError $error) {
    Response::createAndReturn('API_ERROR_GENERIC_AUTH_ERROR', null, $error);
}