<?php

require '../objects/User.php';
(new User(utilNotEmptyOrNull($_POST, 'email'))) -> forgotPassword() -> returnResponse();