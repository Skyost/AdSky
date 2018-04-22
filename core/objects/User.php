<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../AdSky.php';
require_once __DIR__ . '/../Response.php';

require_once __DIR__ . '/Ad.php';

require_once __DIR__ . '/../Utils.php';

use Delight\Auth;

class User {

    private $_email;
    private $_username;
    private $_password;
    private $_type;

    public function __construct($email = null, $password = null, $username = null, $type = null) {
        $this -> _email = $email;
        $this -> _username = $username;
        $this -> _password = $password;
        $this -> _type = $type;
    }

    public function login($rememberDuration = null) {
        $adsky = AdSky::getInstance();
        
        try {
            $auth = $adsky -> getAuth();
            $auth -> login($this -> _email, $this -> _password, $rememberDuration);

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_PASSWORD'), null, $error);
        }
        catch(Auth\EmailNotVerifiedException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AttemptCancelledException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_ATTEMPT_CANCELLED'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    public function register() {
        $adsky = AdSky::getInstance();

        try {
            $auth = $adsky -> getAuth();

            $userId = $auth -> registerWithUniqueUsername($this -> _email, $this -> _password, $this -> _username, function($selector, $token) use ($adsky) {
                try {
                    $this -> sendEmail('Confirm your email', 'confirm.twig', [
                        'selector' => $selector,
                        'token' => $token
                    ]);
                }
                catch(Exception $error) {}
            });

            if($this -> _type != null) {
                $auth -> admin() -> addRoleForUserById($userId, $this -> _type);
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\DuplicateUsernameException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_USERNAME_ALREADYEXISTS'), null, $error);
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_PASSWORD'), null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_EMAIL_ALREADYEXISTS'), null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Auth\UnknownIdException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_UNKNOWN_ID'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    public function update($email = null, $password = null, $type = null) {
        $adsky = AdSky::getInstance();

        try {
            $auth = $adsky -> getAuth();

            $isAdmin = $auth -> hasRole(Auth\Role::ADMIN);
            $currentEmail = $auth -> getEmail();
            $sameEmail = $currentEmail == $this -> _email;

            $admin = $auth -> admin();

            if(!$sameEmail) {
                $admin -> logInAsUserByEmail($this -> _email);
            }

            if(!$auth -> reconfirmPassword($this -> _password) && !$isAdmin) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_CURRENT_PASSWORD'));
            }

            if($this -> _type == null) {
                $this -> _type = $auth -> hasRole(Auth\Role::ADMIN) ? Auth\Role::ADMIN : null;
            }

            if($email != null && $this -> _email != $email) {
                $this -> _email = $email;
                $auth -> changeEmail($email, function($selector, $token) use ($adsky, $auth, $isAdmin, $sameEmail) {
                    if($isAdmin || $sameEmail) {
                        $auth -> confirmEmail($selector, $token);
                        return;
                    }

                    try {
                        $this -> sendEmail('Confirm your email', 'confirm.twig', [
                            'selector' => $selector,
                            'token' => $token
                        ]);
                    }
                    catch(Exception $error) {}
                });
            }

            if($password != null && $this -> _password != $password) {
                $this -> _password = $password;
                $auth -> changePasswordWithoutOldPassword($password);
            }

            if($this -> _type != $type) {
                $this -> _type = $type;

                $admin -> removeRoleForUserByEmail($this -> _email, Auth\Role::ADMIN);

                if($type != null) {
                    $admin -> addRoleForUserByEmail($this -> _email, $type);
                }
            }

            if(!$sameEmail) {
                $admin -> logInAsUserByEmail($currentEmail);
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_EMAIL_ALREADYEXISTS'), null, $error);
        }
        catch(Auth\EmailNotVerifiedException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
        }
        catch(Auth\NotLoggedInException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'), null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_PASSWORD'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    public function delete() {
        $adsky = AdSky::getInstance();

        try {
            Ad::deleteAdsFromUser($this -> _username);
            $admin = $auth = $adsky -> getAuth() -> admin();
            $admin -> deleteUserByUsername($this -> _username);
            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    public function forgotPassword() {
        $adsky = AdSky::getInstance();

        try {
            $auth = $adsky -> getAuth();

            if($auth -> isLoggedIn()) {
                return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'));
            }

            $auth -> forgotPassword($this -> _email, function($selector, $token) use ($adsky, $auth) {
                try {
                    $this -> sendEmail('Password reset', 'reset.twig', [
                        'email' => $this -> _email,
                        'selector' => $selector,
                        'token' => $token
                    ]);
                }
                catch(Exception $error) {}
            });

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(\Delight\Auth\InvalidEmailException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EMAIL'), null, $error);
        }
        catch(\Delight\Auth\EmailNotVerifiedException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_VERIFIED'), null, $error);
        }
        catch(\Delight\Auth\ResetDisabledException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_RESET_DISABLED'), null, $error);
        }
        catch(\Delight\Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(\Delight\Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
    }

    public function confirmReset($selector, $token) {
        $adsky = AdSky::getInstance();

        try {
            $auth = $adsky -> getAuth();

            $password = Auth\Auth::createRandomString(10);

            $auth -> canResetPasswordOrThrow($selector, $token);
            $auth -> resetPassword($selector, $token, $password);
            $this -> sendEmail('Password reset confirmation', 'password.twig', ['password' => $password]);

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\InvalidSelectorTokenPairException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_SELECTOR'), null, $error);
        }
        catch(Auth\TokenExpiredException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOKEN_EXPIRED'), null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\ResetDisabledException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_RESET_DISABLED'), null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return User::confirmReset($selector, $token);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    private function sendEmail($title, $template, $parameters = []) {
        $adsky = AdSky::getInstance();
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../../views/emails/');
        $twig = new Twig_Environment($loader);

        $websiteSettings = $adsky -> getWebsiteSettings();
        $root = $websiteSettings -> getWebsiteRoot();

        $parameters['url'] = $root . (Utils::endsWith($root, '/') ? '' : '/');
        $parameters['settings'] = $adsky -> buildSettingsArray([$adsky -> getAdSettings(), $websiteSettings]);

        $sender = $websiteSettings -> getWebsiteEmail();
        $headers = 'From: ' . $sender . "\r\n";
        $headers .= 'Reply-To: '. $sender . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        try {
            mail($this -> _email, $title, $twig -> render($template, $parameters), $headers);
        }
        catch(Exception $error) {
            throw $error;
        }
    }

    public static function confirmRegistration($selector, $token, $rememberDuration = null) {
        $adsky = AdSky::getInstance();

        try {
            $adsky -> getAuth() -> confirmEmailAndSignIn($selector, $token, $rememberDuration);

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\InvalidSelectorTokenPairException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_SELECTOR'), null, $error);
        }
        catch(Auth\TokenExpiredException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOKEN_EXPIRED'), null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_EMAIL_ALREADYEXISTS'), null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

    public static function getUsers($page = null) {
        $adsky = AdSky::getInstance();
        $pdo = $adsky -> getPDO();

        if($page == null || $page < 1) {
            $page = 1;
        }
        $page = intval($page);

        $result = $pdo -> query('SELECT COUNT(*) FROM `' . ($adsky -> getMySQLSettings() -> getUsersTable()) . '`');
        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

        $rows = $result -> fetchColumn();

        $itemsPerPage = $adsky -> getWebsiteSettings() -> getWebsitePaginatorItemsPerPage();
        $maxPage = ceil($rows / $itemsPerPage);
        if($page > $maxPage) {
            $page = $maxPage;
        }

        $min = ($page - 1) * $itemsPerPage;
        $max = $min + $itemsPerPage;

        if($min != 0) {
            $max = $max - 1;
        }

        $statement = $pdo -> prepare('SELECT `username`, `email`, `verified`, `roles_mask`, `registered`, `last_login` FROM `' . ($adsky -> getMySQLSettings() -> getUsersTable()) . '` ORDER BY `last_login` LIMIT ' . $min . ', ' . $max);
        $result = $statement -> execute();

        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

        $admin = Auth\Role::ADMIN;
        $data = [];
        foreach($statement -> fetchAll() as $row) {
            array_push($data, [
                'username' => $row['username'],
                'email' => $row['email'],
                'type' => $row['roles_mask'] & $admin === $admin ? 0 : 1,
                'verified' => $row['verified'],
                'last_login' => intval($row['last_login']),
                'registered' => intval($row['registered'])
            ]);
        }

        return new Response(null, $adsky -> getLanguageString('API_SUCCESS'), [
            'data' => $data,
            'page' => $page,
            'maxPage' => $maxPage,
            'hasPrevious' => $page > 1,
            'hasNext' => $page < $maxPage
        ]);
    }

    public static function isLoggedIn() {
        $adsky = AdSky::getInstance();
        $auth = $adsky -> getAuth();

        if($auth -> isLoggedIn()) {
            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'), [
                'username' => $auth -> getUsername(),
                'email' => $auth -> getEmail(),
                'type' => $auth -> hasRole(Auth\Role::ADMIN) ? 0 : 1
            ]);
        }

        return new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'), null, null);
    }

    public static function logout() {
        $adsky = AdSky::getInstance();

        try {
            $adsky -> getAuth() -> logOut();

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }
        catch(Exception $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_ERROR'), null, $error);
        }
    }

}