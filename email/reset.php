<?php
require_once '../api/objects/User.php';

$response = (new User(utilNotEmptyOrNull($_GET, 'email'))) -> confirmReset(utilNotEmptyOrNull($_GET, 'selector'), utilNotEmptyOrNull($_GET, 'token'), $auth);

if($response -> _error != null) {
    die($response -> _error);
}

echo $response -> _message;

header('Location: ../login/?message=password_reset');