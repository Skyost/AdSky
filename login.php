<?php

require_once 'api/Settings.php';

if(!$installed) {
    header('Location: install/');
    die();
}

require 'api/objects/User.php';

$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

$parameters = ((new User()) -> isLoggedIn()) -> _object;

if($parameters != null) {
    header('Location: admin.php');
    die();
}

$parameters = [
    'settings' => $settings,
    'user' => $parameters
];

if(!empty($_GET['message'])) {
    $parameters['message'] = $_GET['message'];
}

try {
    echo $twig -> render('login/content.twig', $parameters);
}
catch(Exception $error) {
    echo $error;
}