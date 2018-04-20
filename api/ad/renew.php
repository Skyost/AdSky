<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/renew.php
 * Target : Ads
 * User role : User
 * Description : Renew an ad.
 * Throttle : 5 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P] days : Number of days to add to the current expiration date.
 */

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