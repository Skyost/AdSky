<?php

require '../Lang.php';
require '../objects/User.php';

$auth = createAuth();

$username = empty($_POST['username']) ? $auth -> getEmail() : $_POST['username'];
if($username != $auth -> getUsername() && !$auth -> hasRole(\Delight\Auth\Role::ADMIN)) {
    (new Response($lang['API_ERROR_NOT_ADMIN'])) -> returnResponse();
}
// TODO Vérifier les vulnérabilités : si un utilisateur change son email dans le cookie par exemple.

((new User(null, null, $username)) -> delete($auth)) -> returnResponse();