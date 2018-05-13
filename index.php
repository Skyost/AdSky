<?php

use AdSky\Core\Actions;
use AdSky\Core\Actions\Ad\PayAction;
use AdSky\Core\Actions\Ad\RenewAction;
use AdSky\Core\Actions\Plugin\DeleteExpiredAction;
use AdSky\Core\Actions\Plugin\TodayAdsAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\Actions\Update\CheckAction;
use AdSky\Core\Actions\User\ForgotPasswordAction;
use AdSky\Core\Actions\User\LoginAction;
use AdSky\Core\Actions\User\LogoutAction;
use AdSky\Core\Actions\User\RegisterAction;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use AdSky\Core\Objects\User;
use AdSky\Core\Renderer;
use AdSky\Core\Utils;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/Autoloader.php';

Autoloader::register();
$router = new \Bramus\Router\Router();

$router -> set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo twigTemplate('errors/', '404.twig');
});

$router -> all('/404.html', function() {
    echo twigTemplate('errors/', '404.twig');
});

$router -> all('/', function() {
    echo twigTemplate('index/');
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

    echo twigTemplate('login/');
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

    echo twigTemplate('admin/', 'content.twig', ['user' => $user -> toArray()]);
});

$router -> mount('/api/v1/ads', function() use ($router) {
    $router -> all('/', function() {
        (new Actions\Ad\ListAction(null, Utils::notEmptyOrNull($_POST, 'page'))) -> execute() -> returnResponse();
    });

    $router -> all('/pay', function() {
        (new PayAction(Utils::notEmptyOrNull($_POST, 'type'), Utils::notEmptyOrNull($_POST, 'title'), Utils::notEmptyOrNull($_POST, 'message'), Utils::notEmptyOrNull($_POST, 'interval'), Utils::notEmptyOrNull($_POST, 'expiration'), Utils::notEmptyOrNull($_POST, 'duration')))
            -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)', function($id) {
        (new Actions\Ad\InfoAction($id)) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/delete', function($id) {
        (new Actions\Ad\DeleteAction($id)) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/renew', function($id) {
        (new RenewAction($id, Utils::notEmptyOrNull($_POST, 'days'))) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/update', function($id) {
        (new Actions\Ad\UpdateAction($id, Utils::notEmptyOrNull($_POST, 'type'), Utils::notEmptyOrNull($_POST, 'title'), Utils::notEmptyOrNull($_POST, 'message'), Utils::notEmptyOrNull($_POST, 'interval'), Utils::notEmptyOrNull($_POST, 'expiration'), Utils::notEmptyOrNull($_POST, 'duration')))
            -> execute() -> returnResponse();
    });
});

$router -> mount('/api/v1/plugin', function() use ($router) {
    $router -> all('/delete-expired', function() {
        (new DeleteExpiredAction(Utils::notEmptyOrNull($_POST, 'key'))) -> execute() -> returnResponse();
    });

    $router -> all('/today', function() {
        (new TodayAdsAction(Utils::notEmptyOrNull($_POST, 'key'))) -> execute() -> returnResponse();
    });
});

$router -> mount('/api/v1/update', function() use ($router) {
    $router -> all('/check', function() {
        (new CheckAction()) -> execute() -> returnResponse();
    });

    $router -> all('/update', function() {
        (new Actions\Update\UpdateAction()) -> execute() -> returnResponse();
    });
});

$router -> mount('/api/v1/users', function() use ($router) {
    $router -> all('/', function() {
        (new Actions\User\ListAction(Utils::notEmptyOrNull($_POST, 'page'))) -> execute() -> returnResponse();
    });

    $router -> all('/login', function() {
        (new LoginAction(Utils::notEmptyOrNull($_POST, 'email'), Utils::notEmptyOrNull($_POST, 'password'), Utils::notEmptyOrNull($_POST, 'rememberduration'))) -> execute() -> returnResponse();
    });

    $router -> all('/logout', function() {
        (new LogoutAction()) -> execute() -> returnResponse();
    });

    $router -> all('/register', function() {
        (new RegisterAction(Utils::notEmptyOrNull($_POST, 'username'), Utils::notEmptyOrNull($_POST, 'email'), Utils::notEmptyOrNull($_POST, 'password'))) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)', function($email) {
        (new Actions\User\InfoAction($email)) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/ads', function($email) {
        (new Actions\Ad\ListAction($email, Utils::notEmptyOrNull($_POST, 'page'))) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/delete', function($email) {
        (new Actions\User\DeleteAction($email)) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/forgot', function($email) {
        (new ForgotPasswordAction($email)) -> execute() -> returnResponse();
    });

    $router -> all('/([^/]+)/update', function($email) {
        (new Actions\User\UpdateAction($email, Utils::notEmptyOrNull($_POST, 'oldpassword'), Utils::notEmptyOrNull($_POST, 'email'), Utils::notEmptyOrNull($_POST, 'password'), Utils::notEmptyOrNull($_POST, 'type'), Utils::notEmptyOrNull($_POST, 'force'))) -> execute() -> returnResponse();
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
        return new Response(null, 'API_SUCCESS');
    });
});

$router -> all('/payment/renew(.*)', function() {
    handlePayment('admin/?message=renew_error#list', 'admin/?message=renew_success#list', function(Ad $ad) {
        if(!$ad -> renew($_GET['days'])) {
            return new Response('API_ERROR_INVALID_RENEWDAY');
        }

        $ad -> sendUpdateToDatabase($_GET['id']);
        return new Response(null, 'API_SUCCESS');
    });
});

$router -> run();

function twigTemplate($folder, $file = 'content.twig', $parameters = []) {
    try {
        $adsky = AdSky::getInstance();

        if(!$adsky -> isInstalled()) {
            header('Location: install/');
            die();
        }

        $renderer = new Renderer();
        $renderer -> addRelativePath($folder);

        if(!empty($_GET['message'])) {
            $parameters['message'] = $_GET['message'];
        }

        return $renderer -> renderWithDefaultSettings($file, $parameters);
    }
    catch(Exception $ex) {
        return $ex;
    }
}

function handlePayment($exLink, $successLink, callable $action) {
    try {
        $root = AdSky::getInstance() -> getWebsiteSettings() -> getWebsiteRoot();
        if($_GET['success'] != true) {
            header('Location: ' . $root . $exLink);
            die();
        }

        $adsky = AdSky::getInstance();
        $user = AdSky::getInstance() -> getCurrentUserObject();

        if($user == null) {
            (new Response('API_ERROR_NOT_LOGGEDIN')) -> returnResponse();
        }

        $apiContext = $adsky -> getPayPalSettings() -> getPayPalAPIContext();

        $payment = Payment::get($_GET['paymentId'], $apiContext);

        $execution = new PaymentExecution();
        $execution -> setPayerId($_GET['PayerID']);

        $payment -> execute($execution, $apiContext);

        $ad = isset($_GET['id']) ? Ad::getFromDatabase($_GET['id']) : new Ad($user -> getUsername(), intval($_GET['type']), $_GET['title'], $_GET['message'], intval($_GET['interval']), intval($_GET['expiration']), Utils::notEmptyOrNull($_GET, 'duration'));

        $response = call_user_func_array($action, [$ad]);
        if($response -> _error != null) {
            throw new Exception($response -> _error);
        }

        header('Location: ' . $root . $successLink);
    }
    catch(Exception $ex) {
        echo $ex;
    }
    die();
}