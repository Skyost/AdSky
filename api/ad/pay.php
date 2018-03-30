<?php

require '../Lang.php';

require '../objects/Ad.php';

Ad::pay(false, function($ad, $pdo, $header = null) {
    $response = $ad -> register($pdo);
    if($response -> _error != null) {
        $response -> returnResponse();
    }

    if($header != null) {
        header($header . '../../admin.php?message=create_success#create');
        die();
    }

    global $lang;
    (new Response(null, $lang['API_SUCCESS'], 'admin.php?message=create_success#create')) -> returnResponse();
});