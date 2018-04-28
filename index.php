<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/core/AdSky.php';

require_once __DIR__ . '/core/objects/Ad.php';
require_once __DIR__ . '/core/objects/User.php';

require_once __DIR__ . '/core/settings/AdSettings.php';
require_once __DIR__ . '/core/settings/WebsiteSettings.php';

require_once __DIR__ . '/core/Response.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$router = new \Bramus\Router\Router();

$router -> set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo twigTemplate('errors', '404.twig');
});

$router -> all('/404.html', function() {
    echo twigTemplate('errors', '404.twig');
});

$router -> all('/', function() {
    echo twigTemplate('index');
});

$router -> all('/login/', function() {
    $user = AdSky::getInstance() -> getCurrentUserObject();

    if($user != null) {
        header('Location: ../admin/');
        die();
    }

    echo twigTemplate('login');
});

$router -> all('/admin/', function() {
    $user = AdSky::getInstance() -> getCurrentUserObject();

    if($user == null) {
        header('Location: ../login/');
        die();
    }

    echo twigTemplate('admin', 'content.twig', ['user' => $user]);
});

$router -> all('/api/ad/(.*)', function($operation) {
    $operation = __DIR__ . '/api/ad/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('Ad operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> all('/api/plugin/(.*)', function($operation) {
    $operation = __DIR__ . '/api/plugin/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('Plugin operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> all('/api/user/(.*)', function($operation) {
    $operation = __DIR__ . '/api/user/' . str_replace('-', '_', htmlentities($operation)) . '.php';
    if(!file_exists($operation)) {
        $response = new Response('User operation not found.');
        $response -> returnResponse();
    }

    include $operation;
});

$router -> all('/email/confirm/([^/]+)/(.*)', function($selector, $token) {
    $adsky = AdSky::getInstance();
    $adsky -> getAuth() -> confirmEmailAndSignIn($selector, $token, (int)(60 * 60 * 24 * 365.25));
    header('Location: ' . $adsky -> getWebsiteSettings() -> getWebsiteRoot() . 'admin/?message=validation_success#home');
});

$router -> all('/email/reset/([^/]+)/([^/]+)/(.*)', function($email, $selector, $token) {
    $adsky = AdSky::getInstance();
    $auth = $adsky -> getAuth();

    if(!$auth -> canResetPassword($selector, $token)) {
        die('An unexpected error occurred while trying to reset your password.');
    }

    $password = \Delight\Auth\Auth::createRandomString(10);

    $auth -> resetPassword($selector, $token, $password);
    User::sendEmail($adsky -> getLanguageString('EMAIL_TITLE_RESET_CONFIRMATION'), $email, 'password.twig', ['password' => $password]);
    header('Location: ' . $adsky -> getWebsiteSettings() -> getWebsiteRoot() . 'login/?message=password_reset');
});

$router -> all('/email/update/([^/]+)/(.*)', function($selector, $token) {
    $adsky = AdSky::getInstance();
    $adsky -> getAuth() -> confirmEmailAndSignIn($selector, $token, (int)(60 * 60 * 24 * 365.25));
    header('Location: ' . $adsky -> getWebsiteSettings() -> getWebsiteRoot() . 'admin/?message=profile_updated#profile');
});

$router -> all('/payment/register(.*)', function() {
    handlePayment('admin/?message=create_error#create', 'admin/?message=create_success#create', function(Ad $ad) {
        $ad -> sendUpdateToDatabase();
        return new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'));
    });
});

$router -> all('/payment/renew(.*)', function() {
    handlePayment('admin/?message=renew_error#list', 'admin/?message=renew_success#list', function(Ad $ad) {
        if(!$ad -> renew($_GET['days'])) {
            return new Response(AdSky::getInstance() -> getLanguageString('API_ERROR_INVALID_RENEWDAY'));
        }

        $ad -> sendUpdateToDatabase($_GET['id']);
        return new Response(null, AdSky::getInstance() -> getLanguageString('API_SUCCESS'));
    });
});

$router -> run();

function twigTemplate($folder, $file = 'content.twig', $parameters = []) {
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
        return $twig -> render($folder . '/' . $file, $parameters);
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
        $user = AdSky::getInstance() -> getCurrentUserObject();

        if($user == null) {
            (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
        }

        $apiContext = $adsky -> getPayPalSettings() -> getPayPalAPIContext();

        $payment = Payment::get($_GET['paymentId'], $apiContext);

        $execution = new PaymentExecution();
        $execution -> setPayerId($_GET['PayerID']);

        $payment -> execute($execution, $apiContext);

        $ad = isset($_GET['id']) ? Ad::getFromDatabase($_GET['id']) : new Ad($user -> getUsername(), intval($_GET['type']), $_GET['title'], $_GET['message'], intval($_GET['interval']), intval($_GET['expiration']), Utils ::notEmptyOrNull($_GET, 'duration'));

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