<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/pay.php
 * Target : Ads
 * User role : User
 * Description : Pay for an ad that will be added on the server. An admin's ad will be registered immediately.
 * Throttle : 5 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P] message : Message of the ad.
 * [P] interval : Number of times to display the ad per day.
 * [P] expiration : Expiration date of the ad (timestamp).
 * [P][O] duration : Duration of a Title ad.
 */

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';

Ad::pay(false, function(Ad $ad, $header = null) {
    $response = $ad -> register();
    if($response -> _error != null) {
        $response -> returnResponse();
    }

    if($header != null) {
        header($header . '../../admin/?message=create_success#create');
        die();
    }

    (new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'), 'admin/?message=create_success#create')) -> returnResponse();
});