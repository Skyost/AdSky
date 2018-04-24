<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/core/AdSky.php';
require_once __DIR__ . '/core/objects/User.php';
require_once __DIR__ . '/core/settings/AdSettings.php';
require_once __DIR__ . '/core/settings/WebsiteSettings.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$router = new \Bramus\Router\Router();

$router -> set404(function() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404 ERROR.';
});

$router -> all('/', function() {
    echo twigTemplate('index');
});

$router -> all('/login/', function() {
    $user = new User();
    $parameters = $user -> isLoggedIn() -> _object;

    if($parameters != null) {
        header('Location: ../admin/');
        die();
    }

    echo twigTemplate('login');
});

$router -> all('/admin/', function() {
    $user = new User();
    $parameters = $user -> isLoggedIn() -> _object;

    if($parameters == null) {
        header('Location: ../login/');
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

$router -> all('/email/confirm/([^/]+)/(.*)', function($selector, $token) {
    $response = User::confirmRegistration($selector, $token, (int)(60 * 60 * 24 * 365.25));
    if($response -> _error != null) {
        die($response -> _error);
    }

    header('Location: ' . AdSky::getInstance() -> getWebsiteSettings() -> getWebsiteRoot() . 'admin/?message=validation_success#home');
});

$router -> all('/email/reset/([^/]+)/([^/]+)/(.*)', function($email, $selector, $token) {
    $user = new User($email);
    $response = $user -> confirmReset($selector, $token);

    if($response -> _error != null) {
        die($response -> _error);
    }

    header('Location: ' . AdSky::getInstance() -> getWebsiteSettings() -> getWebsiteRoot() . 'login/?message=password_reset');
});

$router -> all('/payment/register(.*)', function() {
    handlePayment('admin/?message=create_error#create', 'admin/?message=create_success#create', function(Ad $ad) {
        return $ad -> register();
    });
});

$router -> all('/payment/renew(.*)', function() {
    handlePayment('admin/?message=renew_error#list', 'admin/?message=renew_success#list', function(Ad $ad) {
        return $ad -> renew($_GET['days']);
    });
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

function handlePayment($errorLink, $successLink, callable $action) {
    try {
        $root = AdSky::getInstance() -> getWebsiteSettings() -> getWebsiteRoot();
        if($_GET['success'] != true) {
            header('Location: ' . $root . $errorLink);
            die();
        }

        $adsky = AdSky::getInstance();
        $user = User::isLoggedIn() -> _object;

        if($user == null) {
            (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
        }

        $apiContext = $adsky -> getPayPalSettings() -> getPayPalAPIContext();

        $payment = Payment::get($_GET['paymentId'], $apiContext);

        $execution = new PaymentExecution();
        $execution -> setPayerId($_GET['PayerID']);

        $payment -> execute($execution, $apiContext);

        $ad = new Ad($user['username'], intval($_GET['type']), $_GET['title'], $_GET['message'], intval($_GET['interval']), intval($_GET['expiration']), Utils ::notEmptyOrNull($_GET, 'duration'));

        $response = call_user_func_array($action, [$ad]);
        if($response -> _error != null) {
            throw new Exception($response -> _error);
        }

        header('Location: ' . $root . $successLink);
    }
    catch(Exception $error) {
        echo $error;
    }
    die();
}