<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Lang.php';

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../Settings.php';

use Delight\Auth;

define('USERS_TABLE', $settings['DB_PREFIX'] . 'users');

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

    public function login($rememberDuration = null, $auth = null) {
        global $lang;

        try {
            if($auth == null) {
                $auth = createAuth();
            }
            
            $auth -> login($this -> _email, $this -> _password, $rememberDuration);

            return new Response(null, $lang['API_SUCCESS'], null);
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($lang['API_ERROR_INVALID_EMAIL'], null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return new Response($lang['API_ERROR_INVALID_PASSWORD'], null, $error);
        }
        catch(Auth\EmailNotVerifiedException $error) {
            return new Response($lang['API_ERROR_NOT_VERIFIED'], null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AttemptCancelledException $error) {
            return new Response($lang['API_ERROR_ATTEMPT_CANCELLED'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

    public function register($auth = null) {
        global $lang;

        try {
            if($auth == null) {
                $auth = createAuth();
            }
            
            $userId = $auth -> registerWithUniqueUsername($this -> _email, $this -> _password, $this -> _username, function($selector, $token) {
                $this -> sendConfirmationEmail($selector, $token);
            });

            if($this -> _type != null) {
                $auth -> admin() -> addRoleForUserById($userId, $this -> _type);
            }

            return new Response(null, $lang['API_SUCCESS'], null);
        }
        catch(Auth\DuplicateUsernameException $error) {
            return new Response($lang['API_ERROR_USERNAME_ALREADYEXISTS'], null, $error);
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($lang['API_ERROR_INVALID_EMAIL'], null, $error);
        }
        catch(Auth\InvalidPasswordException $error) {
            return new Response($lang['API_ERROR_INVALID_PASSWORD'], null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($lang['API_ERROR_EMAIL_ALREADYEXISTS'], null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Auth\UnknownIdException $error) {
            return new Response($lang['API_ERROR_UNKNOWN_ID'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

    public function confirm($selector, $token, $rememberDuration = null, $auth = null) {
        global $lang;

        try {
            if($auth == null) {
                $auth = createAuth();
            }
            
            $auth -> confirmEmailAndSignIn($selector, $token, $rememberDuration);

            return new Response(null, $lang['API_SUCCESS'], null);
        }
        catch(Auth\InvalidSelectorTokenPairException $error) {
            return new Response($lang['API_ERROR_INVALID_SELECTOR'], null, $error);
        }
        catch(Auth\TokenExpiredException $error) {
            return new Response($lang['API_ERROR_TOKEN_EXPIRED'], null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($lang['API_ERROR_EMAIL_ALREADYEXISTS'], null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

    public function update($email = null, $password = null, $type = null, $auth = null) {
        global $lang;

        try {
            if($auth == null) {
                $auth = createAuth();
            }

            $isAdmin = $auth -> hasRole(Auth\Role::ADMIN);
            $currentEmail = $auth -> getEmail();
            $sameEmail = $currentEmail == $this -> _email;

            $admin = $auth -> admin();

            if(!$sameEmail) {
                $admin -> logInAsUserByEmail($this -> _email);
            }

            if(!$auth -> reconfirmPassword($this -> _password) && !$isAdmin) {
                return new Response($lang['API_ERROR_INVALID_CURRENT_PASSWORD']);
            }

            if($email != null && $this -> _email != $email) {
                $this -> _email = $email;
                $auth -> changeEmail($email, function($selector, $token) use ($auth, $isAdmin, $sameEmail) {
                    if($isAdmin || $sameEmail) { // TODO test it
                        $auth -> confirmEmail($selector, $token);
                        return;
                    }
                    $this -> sendConfirmationEmail($selector, $token);
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

            return new Response(null, $lang['API_SUCCESS'], null);
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($lang['API_ERROR_INVALID_EMAIL'], null, $error);
        }
        catch(Auth\UserAlreadyExistsException $error) {
            return new Response($lang['API_ERROR_EMAIL_ALREADYEXISTS'], null, $error);
        }
        catch(Auth\EmailNotVerifiedException $error) {
            return new Response($lang['API_ERROR_NOT_VERIFIED'], null, $error);
        }
        catch(Auth\NotLoggedInException $error) {
            return new Response($lang['API_ERROR_NOT_LOGGEDIN'], null, $error);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch (Auth\InvalidPasswordException $error) {
            return new Response($lang['API_ERROR_INVALID_PASSWORD'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

    public function delete($pdo = null, $auth = null) {
        require_once __DIR__ . '/Ad.php';
        global $lang;

        if($pdo == null) {
            $pdo = getPDO();
        }

        if($auth == null) {
            $auth = createAuth($pdo);
        }

        try {
            Ad::deleteAdsFromUser($this -> _username, $pdo);
            ($auth -> admin()) -> deleteUserByUsername($this -> _username);
            return new Response(null, $lang['API_SUCCESS']);
        }
        catch(Auth\InvalidEmailException $error) {
            return new Response($lang['API_ERROR_INVALID_EMAIL'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

    // TODO
    private function sendConfirmationEmail($selector, $token) {
        //mail($this -> _email, 'Bravo !', 'You are registered. Please click on the following link to confirm : http://localhost:80/confirm/?selector=' . $selector . '&token=' . $token);
        $this -> confirm($selector, $token);
    }

    public static function getUsers($page = null, $pdo = null) {
        global $lang;

        if($page == null || $page < 1) {
            $page = 1;
        }
        $page = intval($page);

        if($pdo == null) {
            $pdo = getPDO();
        }

        $result = $pdo -> query('SELECT COUNT(*) FROM `' . USERS_TABLE . '`');
        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
        }

        $rows = $result -> fetchColumn();

        global $settings;
        $maxPage = ceil($rows / $settings['PAGINATOR_MAX']);
        if($page > $maxPage) {
            $page = $maxPage;
        }

        $min = ($page - 1) * $settings['PAGINATOR_MAX'];
        $max = $min + $settings['PAGINATOR_MAX'];

        if($min != 0) {
            $max = $max - 1;
        }

        $statement = $pdo -> prepare('SELECT `username`, `email`, `verified`, `roles_mask`, `registered`, `last_login` FROM `' . USERS_TABLE . '` ORDER BY `last_login` LIMIT ' . $min . ', ' . $max);
        $result = $statement -> execute();

        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
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

        return new Response(null, $lang['API_SUCCESS'], [
            'data' => $data,
            'page' => $page,
            'maxPage' => $maxPage,
            'hasPrevious' => $page > 1,
            'hasNext' => $page < $maxPage
        ]);
    }

    public static function isLoggedIn($auth = null) {
        global $lang;

        if($auth == null) {
            $auth = createAuth();
        }

        if($auth -> isLoggedIn()) {
            return new Response(null, $lang['API_SUCCESS'], [
                'username' => $auth -> getUsername(),
                'email' => $auth -> getEmail(),
                'type' => $auth -> hasRole(Auth\Role::ADMIN) ? 0 : 1
            ]);
        }

        return new Response($lang['API_ERROR_NOT_LOGGEDIN'], null, null);
    }

    public static function logout($auth = null) {
        global $lang;

        if($auth == null) {
            $auth = createAuth();
        }

        try {
            $auth -> logOut();

            return new Response(null, $lang['API_SUCCESS'], null);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }
        catch(Exception $error) {
            return new Response($lang['API_ERROR_GENERIC_ERROR'], null, $error);
        }
    }

}