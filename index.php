<?php

require_once 'api/Settings.php';

if(!$installed) {
    header('Location: install/');
    die();
}

$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

$parameters = [
    'settings' => $settings
];

try {
    echo $twig -> render('index/content.twig', $parameters);
}
catch(Exception $error) {
    echo $error;
}