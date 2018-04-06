<?php
require_once '../api/objects/User.php';

$response = User::confirmRegistration(utilNotEmptyOrNull($_GET, 'selector'), utilNotEmptyOrNull($_GET, 'token'), (int)(60 * 60 * 24 * 365.25));
if($response -> _error != null) {
    die($response -> _error);
}

header('Location: ../admin.php?message=validation_success#home');