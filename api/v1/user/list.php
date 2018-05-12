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

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Utils.php';

require_once __DIR__ . '/../../../core/Response.php';

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
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
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
catch(Delight\Auth\TooManyRequestsException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
    $response -> returnResponse();
}
catch(Delight\Auth\AuthError $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
    $response -> returnResponse();
}