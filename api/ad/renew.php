<?php

require '../Lang.php';

require '../objects/Ad.php';

Ad::pay(true, function($ad, $pdo, $header = null) {
    $response = $ad -> renew(intval($_POST['days']), $pdo);
    if($response -> _error != null) {
        $response -> returnResponse();
    }

    if($header != null) {
        header($header . '../../admin.php?message=renew_success#list');
        die();
    }

    global $lang;
    (new Response(null, $lang['API_SUCCESS'], 'admin.php?message=renew_success#list')) -> returnResponse();
});