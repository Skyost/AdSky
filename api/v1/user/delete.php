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

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Response;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

Autoloader::register();
$adsky = AdSky::getInstance();

try {
    $user = $adsky -> getCurrentUserObject();

    // We check if the current user is logged in.
    if($user == null) {
        Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
    }

    // If it's okay, we can check which user we want to delete.
    $auth = $user -> getAuth();
    $email = empty($_POST['email']) || $_POST['email'] == 'current' ? $auth -> getEmail() : $_POST['email'];
    if($email != $user -> getEmail() && !$user -> isAdmin()) {
        Response::createAndReturn('API_ERROR_NOT_ADMIN');
    }

    // We get its username by its email.
    $username = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getUsersTable(), 'username', ['email' => $email]);
    if(empty($username)) {
        throw new Auth\InvalidEmailException();
    }

    // And then we delete him.
    $username = $username[0];
    $auth -> admin() -> deleteUserByUsername($username);

    $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);

    Response::createAndReturn(null, 'API_SUCCESS');
}
catch(PDOException $error) {
    Response::createAndReturn('API_ERROR_MYSQL_ERROR', null, $error);
}
catch(Auth\InvalidEmailException $error) {
    Response::createAndReturn('API_ERROR_INVALID_EMAIL', null, $error);
}
catch(Exception $error) {
    Response::createAndReturn('API_ERROR_GENERIC_ERROR', null, $error);
}