<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../AdSky.php';

use Delight\Auth;

class User {

    const TYPE_ADMIN = 0;
    const TYPE_PUBLISHER = 1;

    private $_email;
    private $_username;
    private $_type;

    private $_auth;

    /**
     * Creates a new User instance.
     *
     * @param Auth\Auth $auth The PHP-Auth object. The user will be created from it.
     * @param String $email User's email. If null, will be deduced from the PHP-Auth object.
     * @param String $username User's name. If null, will be deduced from the PHP-Auth object.
     * @param String $type User's type (Admin / publisher). If null, will be deduced from the PHP-Auth object.
     */

    public function __construct(Auth\Auth $auth = null, $email = null, $username = null, $type = null) {
        if($auth == null) {
            $auth = AdSky::getInstance() -> getAuth();
        }

        $this -> _email = $email == null ? $auth -> getEmail() : $email;
        $this -> _username = $username == null ? $auth -> getUsername() : $username;

        if($type == null) {
            $this -> _type = $auth -> hasRole(Auth\Role::ADMIN) ? self::TYPE_ADMIN : self::TYPE_PUBLISHER;
            return;
        }

        $this -> _type = $type;

        $this -> _auth = $auth;
    }

    /**
     * Needed if you want to edit some user's traits (email, ...).
     * NOTE : After editing the user, it's better to login back as the old user in PHP-Auth.
     *
     * @return String The old PHP-Auth email.
     *
     * @throws Auth\AuthError Generic PHP-Auth error.
     * @throws Auth\EmailNotVerifiedException If the target email has not been verified yet.
     * @throws Auth\InvalidEmailException If the target email is invalid.
     */

    public function loginAsUserIfNeeded() {
        if($this -> _email == $this -> _auth -> getEmail()) {
            return $this -> _email;
        }

        $currentAuthEmail = $this -> _auth -> getEmail();
        $this -> _auth -> admin() -> logInAsUserByEmail($this -> _email);
        return $currentAuthEmail;
    }

    /**
     * Gets the corresponding PHP-Auth object.
     *
     * @return Auth\Auth The PHP-Auth object.
     */

    public function getAuth() {
        return $this -> _auth;
    }

    /**
     * Gets user's email.
     *
     * @return String User's email.
     */

    public function getEmail() {
        return $this -> _email;
    }

    /**
     * Sets user's email.
     *
     * @param $email User's email.
     * @param bool $verify If a verification should be sent to the new email.
     *
     * @throws Auth\AuthError Generic PHP-Auth error.
     * @throws Auth\EmailNotVerifiedException If the target email has not been verified yet.
     * @throws Auth\InvalidEmailException If the target email is invalid.
     * @throws Auth\NotLoggedInException If user is not logged in via PHP-Auth.
     * @throws Auth\TooManyRequestsException Throttle protection.
     * @throws Auth\UserAlreadyExistsException If the target email already exists.
     */

    public function setEmail($email, $verify = true) {
        $this -> _email = $email;

        if($this -> _auth -> getEmail() == $email) {
            return;
        }

        $this -> _auth -> changeEmail($email, function($selector, $token) use ($verify) {
            if(!$verify) {
                $this -> _auth -> confirmEmail($selector, $token);
                return;
            }

            self::sendEmail('Confirm your email', $this -> _email, 'confirm.twig', [
                'selector' => $selector,
                'token' => $token
            ]);
        });
    }

    /**
     * Gets user's name.
     *
     * @return String The username.
     */

    public function getUsername() {
        return $this -> _username;
    }

    /**
     * Gets user's type.
     *
     * @return int The type.
     */

    public function getType() {
        return $this -> _type;
    }

    /**
     * Checks if the current user is an admin.
     *
     * @return bool Whether the current user is an admin.
     */

    public function isAdmin() {
        return $this -> _type == self::TYPE_ADMIN;
    }

    /**
     * Checks if the current user is a a publisher.
     *
     * @return bool Whether the current user is a publisher.
     */

    public function isPublisher() {
        return !$this -> isAdmin();
    }

    /**
     * Sets user's type.
     *
     * @param int $type The type.
     *
     * @throws Auth\InvalidEmailException If the target email is invalid.
     */

    public function setType($type = self::TYPE_PUBLISHER) {
        $this -> _type = $type;
        $currentAuthType = $this -> _auth -> hasRole(Auth\Role::ADMIN) ? self::TYPE_ADMIN : self::TYPE_PUBLISHER;

        if($currentAuthType == $type) {
            return;
        }

        if($this -> isAdmin()) {
            $this -> _auth -> admin() -> addRoleForUserByEmail($this -> _email, $type);
        }
        else {
            $this -> _auth -> admin() -> removeRoleForUserByEmail($this -> _email, Auth\Role::ADMIN);
        }
    }

    /**
     * Registers an user and returns its corresponding object.
     *
     * @param String $username The username.
     * @param String $email The email.
     * @param String $password The password.
     * @param bool $confirm If a confirmation email should be sent.
     * @param int $type The user's type.
     *
     * @return User The corresponding object.
     *
     * @throws Auth\AuthError Generic PHP-Auth error.
     * @throws Auth\DuplicateUsernameException If the username already exists.
     * @throws Auth\InvalidEmailException If the target email is invalid.
     * @throws Auth\InvalidPasswordException If the password is invalid.
     * @throws Auth\TooManyRequestsException Throttle protection.
     * @throws Auth\UnknownIdException If the user ID is invalid.
     * @throws Auth\UserAlreadyExistsException If the target email already exists.
     */

    public static function register($username, $email, $password, $confirm = true, $type = (AdSky::APP_DEBUG ? self::TYPE_ADMIN : self::TYPE_PUBLISHER)) {
        $auth = AdSky::getInstance() -> getAuth();
        $id = $auth -> registerWithUniqueUsername($email, $password, $username, function($selector, $token) use ($email, $confirm) {
            self::sendEmail('Confirm your email', $email, 'confirm.twig', [
                'selector' => $selector,
                'token' => $token
            ]);
        });

        if($type == self::TYPE_ADMIN) {
            $auth -> admin() -> addRoleForUserById($id, self::TYPE_ADMIN);
        }

        return new User($auth);
    }

    /**
     * Sends an email.
     *
     * @param String $title Email's title.
     * @param String $email Target's email.
     * @param String $template Template file.
     * @param array $parameters Parameters (used by twig).
     */

    public static function sendEmail($title, $email, $template, $parameters = []) {
        try {
            $adsky = AdSky::getInstance();
            $loader = new Twig_Loader_Filesystem(__DIR__ . '/../../views/emails/');
            $twig = new Twig_Environment($loader);

            $websiteSettings = $adsky -> getWebsiteSettings();

            $parameters['url'] = $websiteSettings -> getWebsiteRoot();
            $parameters['settings'] = $adsky -> buildSettingsArray([$adsky -> getAdSettings(), $websiteSettings]);

            $sender = $websiteSettings -> getWebsiteEmail();
            $headers = 'From: ' . $sender . "\r\n";
            $headers .= 'Reply-To: ' . $sender . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            mail($email, $title, $twig -> render($template, $parameters), $headers);
        }
        catch(Twig_Error_Loader $error) {
            mail($email, $title, $error);
        }
        catch(Twig_Error_Syntax $error) {
            mail($email, $title, $error);
        }
        catch(Twig_Error_Runtime $error) {
            mail($email, $title, $error);
        }
    }

    public function toArray() {
        return [
            'username' => $this -> _username,
            'email' => $this -> _email,
            'type' => $this -> _type
        ];
    }

    public function __toString() {
        return json_encode($this -> toArray());
    }

}