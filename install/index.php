<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>

    <link rel="icon" type="image/png" href="../assets/img/logo.png"/>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="install.css">
</head>
<body>
    <header>
    <h1>Installation</h1>

<?php

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Renderer;
use AdSky\Core\Utils;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/Autoloader.php';

Autoloader::register();
if(empty($_POST['step'])) {
    $_POST['step'] = 1;
}

$adsky = AdSky::getInstance();
$renderer = new Renderer('settings/');

$parameters = [];

if($_POST['step'] == 1) {
    if(version_compare(PHP_VERSION, '5.6.0') < 0) {
        $parameters['error'] = 'php';
    }
    else if(!extension_loaded('pdo') || !extension_loaded('mysqlnd')) {
        $parameters['error'] = 'pdo';
    }
    else if(!extension_loaded('openssl')) {
        $parameters['error'] = 'openssl';
    }
}

else if($_POST['step'] == 2) {
    if(empty($_POST['form-mysql-host']) || empty($_POST['form-mysql-port']) || empty($_POST['form-mysql-db-name']) || empty($_POST['form-mysql-db-username'])) {
        $_POST['step'] = 1;
        $parameters['error'] = 'form';
    }
    else {
        try {
            $pdo = new \PDO('mysql:host=' . $_POST['form-mysql-host'] . ';port=' . $_POST['form-mysql-port'] . ';dbname=' . $_POST['form-mysql-db-name'] . ';charset=utf8mb4', $_POST['form-mysql-db-username'], Utils::notEmptyOrNull($_POST, 'form-mysql-db-password'));
            $mySQLVersion = explode('-', $pdo -> query('SELECT VERSION()') -> fetchColumn())[0];

            if(version_compare($mySQLVersion, '5.5.3') < 0) {
                $parameters['error'] = 'mysql';
            }
            else {
                file_put_contents('../core/settings/MySQLSettings.php', '<?php' . $renderer -> render('MySQLSettings.twig', ['post' => $_POST]));
            }
        }
        catch(Exception $ex) {
            $_POST['step'] = 1;
            $parameters['error'] = 'form';
        }
    }
}

else if($_POST['step'] == 3) {
    try {
        $pdo = $adsky -> getPDO();

        $statement = $pdo -> prepare(file_get_contents('sql/phpAuth.sql'));
        $statement -> execute();
        $statement -> closeCursor();

        $statement = $pdo -> prepare(file_get_contents('sql/ads.sql'));
        $statement -> execute();
        $statement -> closeCursor();
    }
    catch(Exception $ex) {
        $_POST['step'] = 2;
        $parameters['error'] = 'tables';
        $parameters['data'] = $ex;
    }
}

else if($_POST['step'] == 4) {
    if(empty($_POST['form-website-title']) || empty($_POST['form-website-subtitle']) || empty($_POST['form-website-root']) || empty($_POST['form-user-username']) || empty($_POST['form-user-email']) || empty($_POST['form-user-password']) || empty($_POST['form-user-password-confirm']) || $_POST['form-user-password'] != $_POST['form-user-password-confirm']) {
        $_POST['step'] = 3;
        $parameters['error'] = 'form';
    }
    else {
        file_put_contents('../core/settings/WebsiteSettings.php', '<?php' . $renderer -> render('WebsiteSettings.twig', ['post' => $_POST]));

        $auth = $adsky -> getAuth();
        if(!$auth -> isLoggedIn()) {
            try {
                $userId = $auth -> registerWithUniqueUsername($_POST['form-user-email'], $_POST['form-user-password'], $_POST['form-user-username']);
                //$auth -> login($_POST['form-user-email'], $_POST['form-user-password'], (int)(60 * 60 * 24 * 365.25));
                $auth -> admin() -> addRoleForUserById($userId, \Delight\Auth\Role::ADMIN);
            }
            catch(Exception $ex) {
                $_POST['step'] = 3;
                $parameters['error'] = 'auth';
                $parameters['data'] = $ex;
            }
        }
    }
}

else if($_POST['step'] == 5) {
    if(empty($_POST['form-paypal-client-id']) || empty($_POST['form-paypal-client-secret'])) {
        $_POST['step'] = 4;
        $parameters['error'] = 'form';
    }
    else {
        file_put_contents('../core/settings/PayPalSettings.php', '<?php' . $renderer -> render('PayPalSettings.twig', ['post' => $_POST]));

        $pluginSettings = $adsky -> getPluginSettings();

        if($pluginSettings == null) {
            $salt = str_shuffle(md5(microtime()));
            $parameters['data'] = crypt(microtime().rand(), substr($salt, 0, rand(5, strlen($salt))));

            file_put_contents('../core/settings/PluginSettings.php', '<?php' . $renderer -> render('PluginSettings.twig', ['plugin_key' => $parameters['data']]));
        }
        else {
            $parameters['data'] = $pluginSettings -> getPluginKey();
        }

    }
}

showStep($_POST['step'], $parameters);

function showStep($step = 1, $parameters) {
    echo '<small>Step ' . $step . ' / 5</small></header><div id="content">';

    $loader = new Twig_Loader_Filesystem('views/');
    $twig = new Twig_Environment($loader);

    try {
        echo $twig -> render('step-' . $step . '.twig', $parameters);
    }
    catch(Exception $ex) {
        echo $ex;
    }

    echo '</div>';
}
?>

</body>
</html>