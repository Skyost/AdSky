<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/core/AdSky.php';
require_once __DIR__ . '/core/objects/User.php';
require_once __DIR__ . '/core/settings/AdSettings.php';
require_once __DIR__ . '/core/settings/WebsiteSettings.php';

if(!empty(AdSky::APP_DEBUG) && AdSky::APP_DEBUG) {
    $_POST = array_merge($_POST, $_GET);
}

$router = new \Bramus\Router\Router();

$router -> set404(function() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404 ERROR.';
});

$router -> get('/', function() {
    echo twigTemplate('index');
});

$router -> get('/login', function() {
    $user = new User();
    $parameters = $user -> isLoggedIn() -> _object;

    if($parameters != null) {
        header('Location: ../admin');
        die();
    }

    echo twigTemplate('login');
});

$router -> get('/admin', function() {
    $user = new User();
    $parameters = $user -> isLoggedIn() -> _object;

    if($parameters == null) {
        header('Location: ../login');
        die();
    }

    echo twigTemplate('admin', ['user' => $parameters]);
});

$router -> all('/api/ad/(\w+)', function($operation) {
    $operation = __DIR__ . '/api/ad/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('Ad operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> all('/api/plugin/(\w+)', function($operation) {
    $operation = __DIR__ . '/api/plugin/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('Plugin operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> all('/api/user/(\w+)', function($operation) {
    $operation = __DIR__ . '/api/user/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('User operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> run();

function twigTemplate($folder, $parameters = []) {
    $adsky = AdSky::getInstance();

    if(!$adsky -> isInstalled()) {
        header('Location: install/');
        die();
    }

    $loader = new Twig_Loader_Filesystem('views/');
    $twig = new Twig_Environment($loader);

    $parameters['settings'] = $adsky -> buildSettingsArray([$adsky -> getAdSettings(), $adsky -> getWebsiteSettings()]);

    if(!empty($_GET['message'])) {
        $parameters['message'] = $_GET['message'];
    }

    try {
        return $twig -> render($folder . '/content.twig', $parameters);
    }
    catch(Exception $error) {
        return $error;
    }
}