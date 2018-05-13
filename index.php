<?php

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Objects\User;
use AdSky\Core\Response;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/Autoloader.php';

Autoloader::register();
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
    $adsky = AdSky::getInstance();
    if(!$adsky -> isInstalled()) {
        header('Location: ../install/');
        die();
    }

    $user = $adsky -> getCurrentUserObject();

    if($user != null) {
        header('Location: ' . $adsky -> getWebsiteSettings() -> getWebsiteRoot() . 'admin/');
        die();
    }

    echo twigTemplate('login');
});

$router -> all('/admin/', function() {
    $adsky = AdSky::getInstance();
    if(!$adsky -> isInstalled()) {
        header('Location: ../install/');
        die();
    }

    $user = $adsky -> getCurrentUserObject();

    if($user == null) {
        header('Location: ' . $adsky -> getWebsiteSettings() -> getWebsiteRoot() . 'login/');
        die();
    }

    echo twigTemplate('admin', 'content.twig', ['user' => $user]);
});

$router -> mount('/api/v1/ads', function() use ($router) {
    $router -> all('/', function() {
        include __DIR__ . '/api/v1/ad/list.php';
    });

    $router -> all('/pay', function() {
        include __DIR__ . '/api/v1/ad/pay.php';
    });

    $router -> all('/([^/]+)', function($id) {
        $_POST['id'] = $id;
        include __DIR__ . '/api/v1/ad/info.php';
    });

    $router -> all('/([^/]+)/delete', function($id) {
        $_POST['id'] = $id;
        include __DIR__ . '/api/v1/ad/delete.php';
    });

    $router -> all('/([^/]+)/renew', function($id) {
        $_POST['id'] = $id;
        include __DIR__ . '/api/v1/ad/renew.php';
    });

    $router -> all('/([^/]+)/update', function($id) {
        $_POST['id'] = $id;
        include __DIR__ . '/api/v1/ad/update.php';
    });
});

$router -> mount('/api/v1/plugin', function() use ($router) {
    $router -> all('/delete-expired', function() {
        include __DIR__ . '/api/v1/plugin/delete_expired.php';
    });

    $router -> all('/today', function() {
        include __DIR__ . '/api/v1/plugin/today.php';
    });
});

$router -> mount('/api/v1/update', function() use ($router) {
    $router -> all('/check', function() {
        include __DIR__ . '/api/v1/update/check.php';
    });

    $router -> all('/update', function() {
        include __DIR__ . '/api/v1/update/update.php';
    });
});

$router -> mount('/api/v1/users', function() use ($router) {
    $router -> all('/', function() {
        include __DIR__ . '/api/v1/user/list.php';
    });

    $router -> all('/login', function() {
        include __DIR__ . '/api/v1/user/login.php';
    });

    $router -> all('/logout', function() {
        include __DIR__ . '/api/v1/user/logout.php';
    });

    $router -> all('/register', function() {
        include __DIR__ . '/api/v1/user/register.php';
    });

    $router -> all('/([^/]+)', function($email) {
        $_POST['email'] = $email;
        include __DIR__ . '/api/v1/user/info.php';
    });

    $router -> all('/([^/]+)/ads', function($email) {
        $_POST['email'] = $email;
        include __DIR__ . '/api/v1/ad/list.php';
    });

    $router -> all('/([^/]+)/delete', function($email) {
        $_POST['email'] = $email;
        include __DIR__ . '/api/v1/user/delete.php';
    });

    $router -> all('/([^/]+)/forgot', function($email) {
        $_POST['email'] = $email;
        include __DIR__ . '/api/v1/user/forgot_password.php';
    });

    $router -> all('/([^/]+)/update', function($email) {
        $_POST['oldemail'] = $email;
        include __DIR__ . '/api/v1/user/update.php';
    });
});

$router -> all('/api/(.*)', function() {
    $response = new Response('Please check the API documentation here : ' . AdSky::APP_WEBSITE . '.');
    $response -> returnResponse();
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

    $settings = $adsky -> buildSettingsArray([$adsky -> getAdSettings(), $adsky -> getWebsiteSettings()]);
    $settings['PAYPAL_CURRENCY'] = $adsky -> getPayPalSettings() -> getPayPalCurrency();
    $parameters['settings'] = $settings;

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
            Response::createAndReturn('API_ERROR_NOT_LOGGEDIN');
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