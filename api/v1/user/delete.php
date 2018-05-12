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
 * [P][O] email : User to delete. If you do not specify an user, it will delete your account and all your ads. An admin can delete anyone's account.
 */

require_once __DIR__ . '/../../../core/AdSky.php';
require_once __DIR__ . '/../../../core/objects/Ad.php';
require_once __DIR__ . '/../../../core/objects/User.php';

require_once __DIR__ . '/../../../core/Response.php';

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
    $email = empty($_POST['email']) || $_POST['email'] == 'current' ? $auth -> getEmail() : $_POST['email'];
    if($email != $user -> getEmail() && !$user -> isAdmin()) {
        $response = new Response($adsky -> getLanguageString('API_ERROR_NOT_ADMIN'));
        $response -> returnResponse();
    }

    // We get its username by its email.
    $username = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getUsersTable(), 'username', ['email' => $email]);
    if(empty($username)) {
        throw new \Delight\Auth\InvalidEmailException();
    }

    // And then we delete him.
    $username = $username[0];
    $auth -> admin() -> deleteUserByUsername($username);

    $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);

    $response = new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    $response -> returnResponse();
}
catch(PDOException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
    $response -> returnResponse();
}
catch(\Delight\Auth\InvalidEmailException $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
    $response -> returnResponse();
}
catch(Exception $error) {
    $response = new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
    $response -> returnResponse();
}