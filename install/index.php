<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>

    <link rel="icon" type="image/png" href="../assets/img/logo.png"/>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="install.css">
</head>
<body>
    <header>
    <h1>Installation</h1>

<?php

require_once __DIR__ . '/../vendor/autoload.php';

include '../api/Settings.php';

if(empty($_POST['step'])) {
    $_POST['step'] = 1;
}

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
            $pdo = new \PDO('mysql:host=' . $_POST['form-mysql-host'] . ';port=' . $_POST['form-mysql-port'] . ';dbname=' . $_POST['form-mysql-db-name'] . ';charset=utf8mb4', $_POST['form-mysql-db-username'], empty($_POST['form-mysql-db-password']) ? '' : $_POST['form-mysql-db-password']);
            $mySQLVersion = explode('-', $pdo -> query('SELECT VERSION()') -> fetchColumn())[0];

            if(version_compare($mySQLVersion, '5.5.3') < 0) {
                $parameters['error'] = 'mysql';
            }
            else {
                $newMySQLSettings = '$settings[\'DB_HOST\'] = \'' . $_POST['form-mysql-host'] . "';\n";
                $newMySQLSettings .= '$settings[\'DB_PORT\'] = ' . $_POST['form-mysql-port'] . ";\n";
                $newMySQLSettings .= '$settings[\'DB_NAME\'] = \'' . $_POST['form-mysql-db-name'] . "';\n";
                $newMySQLSettings .= '$settings[\'DB_USER\'] = \'' . $_POST['form-mysql-db-username'] . "';\n";
                $newMySQLSettings .= '$settings[\'DB_PASSWORD\'] = \'' . (empty($_POST['form-mysql-db-password']) ? '' : $_POST['form-mysql-db-password']) . "';\n";

                file_put_contents('../api/settings/MySQL.php', "<?php\n" . $newMySQLSettings);
            }
        }
        catch(Exception $error) {
            $_POST['step'] = 1;
            $parameters['error'] = 'form';
        }
    }
}

else if($_POST['step'] == 3) {
    try {
        $pdo = getPDO();
        $pdo -> query(file_get_contents('sql/phpAuth.sql')) -> execute();
        $pdo -> query(file_get_contents('sql/ads.sql')) -> execute();
    }
    catch(Exception $error) {
        $_POST['step'] = 2;
        $parameters['error'] = 'tables';
        $parameters['data'] = $error;
    }

}

else if($_POST['step'] == 4) {
    if(empty($_POST['form-website-title']) || empty($_POST['form-website-subtitle']) || empty($_POST['form-website-link']) || empty($_POST['form-user-username']) || empty($_POST['form-user-email']) || empty($_POST['form-user-password']) || empty($_POST['form-user-password-confirm']) || $_POST['form-user-password'] != $_POST['form-user-password-confirm']) {
        $_POST['step'] = 3;
        $parameters['error'] = 'form';
    }
    else {
        $newWebsiteSettings = '$settings[\'WEBSITE_TITLE\'] = \'' . htmlspecialchars($_POST['form-website-title']) . "';\n";
        $newWebsiteSettings .= '$settings[\'WEBSITE_SUBTITLE\'] = \'' . htmlspecialchars($_POST['form-website-subtitle']) . "';\n";
        $newWebsiteSettings .= '$settings[\'WEBSITE_LINK\'] = \'' . $_POST['form-website-link'] . "';\n";

        file_put_contents('../api/settings/Website.php', "<?php\n" . $newWebsiteSettings);
        $auth = createAuth();

        if($auth -> isLoggedIn()) {
            $parameters['data'] = $settings['PLUGIN_KEY'];
        }
        else {
            try {
                $userId = $auth -> registerWithUniqueUsername($_POST['form-user-email'], $_POST['form-user-password'], $_POST['form-user-username']);
                $auth -> login($_POST['form-user-email'], $_POST['form-user-password'], (int)(60 * 60 * 24 * 365.25));
                $auth -> admin() -> addRoleForUserById($userId, \Delight\Auth\Role::ADMIN);
            }
            catch(Exception $error) {
                $_POST['step'] = 3;
                $parameters['error'] = 'auth';
                $parameters['data'] = $error;
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
        $newPayPalSettings = '$settings[\'PAYPAL_CLIENT_ID\'] = \'' . $_POST['form-paypal-client-id'] . "';\n";
        $newPayPalSettings .= '$settings[\'PAYPAL_CLIENT_SECRET\'] = \'' . $_POST['form-paypal-client-secret'] . "';\n";

        file_put_contents('../api/settings/PayPal.php', "<?php\n" . $newPayPalSettings);

        $salt = str_shuffle(md5(microtime()));
        $parameters['data'] = crypt(microtime().rand(), substr($salt, 0, rand(5, strlen($salt))));

        file_put_contents('../api/settings/Plugin.php', "<?php\n" . '$settings[\'PLUGIN_KEY\'] = \'' . $parameters['data'] . '\';');
    }
}

showStep($_POST['step'], $parameters);

function showStep($step = 1, $parameters) {
    echo '<small>Step ' . $step . ' / 5</small></header><div id="content">';

    $loader = new Twig_Loader_Filesystem('views/');
    $twig = new Twig_Environment($loader);
    echo $twig -> render('step-' . $step . '.twig', $parameters);

    echo '</div>';
}
?>

</body>
</html>