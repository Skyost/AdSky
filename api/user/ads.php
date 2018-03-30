<?php

require '../objects/Ad.php';

if(empty($_POST['username'])) {
    (new Response('Username not set.')) -> returnResponse();
}

(Ad :: getAds(utilNotEmptyOrNull($_POST, 'page'), $_POST['username'])) -> returnResponse();