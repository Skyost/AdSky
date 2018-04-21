<?php

/**
 * ADSKY API FILE
 *
 * Name : ad/delete.php
 * Target : Ads
 * User role : User
 * Description : Delete an ad.
 * Throttle : 10 requests per 60 seconds.
 *
 * Parameters :
 * [P] type : Type of the ad (Title / Chat).
 * [P] title : Title of the ad.
 * [P][O] username : Username of the owner of the ad. If not sent, it will be set to the current user's username. An admin can delete anyone's ad.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../../core/AdSky.php';
require_once __DIR__ . '/../../core/objects/Ad.php';
require_once __DIR__ . '/../../core/objects/User.php';

$adsky = AdSky::getInstance();
$language = $adsky -> getLanguage();

if(!isset($_POST['type']) || strlen($_POST['type']) === 0 || empty($_POST['title'])) {
    $response = new Response($language -> formatNotSet([$language -> getSettings('API_ERROR_NOT_SET_TITLE'), $language -> getSettings('API_ERROR_NOT_SET_TYPE')]));
    $response -> returnResponse();
}

$object = User::isLoggedIn() -> _object;
if($object == null) {
    $response = new Response($language -> getSettings('API_ERROR_NOT_LOGGEDIN'));
    $response -> returnResponse();
}

if(empty($_POST['username'])) {
    $_POST['username'] = $object['username'];
}
else if($object['username'] != $_POST['username'] && $object['type'] !== Ad::TYPE_TITLE) {
    $response = new Response($language -> getSettings('API_ERROR_NOT_ADMIN'));
    $response -> returnResponse();
}

$response = (new Ad($_POST['username'], intval($_POST['type']), $_POST['title'])) -> delete();
$response -> returnResponse();