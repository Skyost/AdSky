<?php

/**
 * ADSKY API FILE
 *
 * Name : user/delete.php
 * Target : User
 * User role : User
 * Description : Delete an user.
 * Throttle : None.
 *
 * Parameters :
 * [P][O] username : User to delete. If you do not specify an user, it will delete your account and all your ads. An admin can delete anyone's account.
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';
require_once __DIR__ . '/../../core/objects/User.php';

require_once __DIR__ . '/../../core/Response.php';

$adsky = AdSky::getInstance();

try {
    $user = $adsky -> getCurrentUserObject();

    // We check if the current user is logged in.
    if($user == null) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'));
        $response -> returnResponse();
    }

    // If it's okay, we can check which user we want to delete.
    $auth = $user -> getAuth();
    $username = empty($_POST['username']) ? $auth -> getUsername() : $_POST['username'];
    if($username != $user -> getUsername() && !$user -> isAdmin()) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    // And we delete him.
    $auth -> admin() -> deleteUserByUsername($username);

    $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\UnknownUsernameException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_USER_NOT_FOUND'), null, $error);
    $response -> returnResponse();
}
catch(Exception $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
    $response -> returnResponse();
}