<?php

namespace AdSky\Core\Objects;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Autoloader.php';

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Renderer;
use Delight\Auth;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Represents an user.
 */

class User {

    const TYPE_ADMIN = 0;
    const TYPE_PUBLISHER = 1;

    private $email;
    private $username;
    private $type;

    private $auth;

    /**
     * Creates a new User instance.
     *
     * @param Auth\Auth $auth The PHP-Auth object. The user will be created from it.
     * @param string $email User's email. If null, will be deduced from the PHP-Auth object.
     * @param string $username User's name. If null, will be deduced from the PHP-Auth object.
     * @param string $type User's type (Admin / publisher). If null, will be deduced from the PHP-Auth object.
     */

    public function __construct($auth = null, $email = null, $username = null, $type = null) {
        Autoloader::register();

        if($auth == null) {
            $auth = AdSky::getInstance() -> getAuth();
        }

        $this -> auth = $auth;

        $this -> email = $email == null ? $auth -> getEmail() : $email;
        $this -> username = $username == null ? $auth -> getUsername() : $username;

        if($type == null) {
            $this -> type = $auth -> hasRole(Auth\Role::ADMIN) ? self::TYPE_ADMIN : self::TYPE_PUBLISHER;
            return;
        }

        $this -> type = $type;
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
        if($this -> email == $this -> auth -> getEmail()) {
            return $this -> email;
        }

        $currentAuthEmail = $this -> auth -> getEmail();
        $this -> auth -> admin() -> logInAsUserByEmail($this -> email);

        $this -> username = $this -> auth -> getUsername();
        $this -> type = $this -> auth -> hasRole(Auth\Role::ADMIN) ? self::TYPE_ADMIN : self::TYPE_PUBLISHER;

        return $currentAuthEmail;
    }

    /**
     * Gets the corresponding PHP-Auth object.
     *
     * @return Auth\Auth The PHP-Auth object.
     */

    public function getAuth() {
        return $this -> auth;
    }

    /**
     * Gets user's email.
     *
     * @return String User's email.
     */

    public function getEmail() {
        return $this -> email;
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
        $this -> email = $email;

        if($this -> auth -> getEmail() == $email) {
            return;
        }

        $this -> auth -> changeEmail($email, function($selector, $token) use ($verify) {
            if(!$verify) {
                $this -> auth -> confirmEmailAndSignIn($selector, $token);
                return;
            }

            self::sendEmail(AdSky::getInstance() -> getLanguageString('EMAIL_TITLE_UPDATE'), $this -> email, 'update.twig', [
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
        return $this -> username;
    }

    /**
     * Gets user's type.
     *
     * @return int The type.
     */

    public function getType() {
        return $this -> type;
    }

    /**
     * Checks if the current user is an admin.
     *
     * @return bool Whether the current user is an admin.
     */

    public function isAdmin() {
        return $this -> type == self::TYPE_ADMIN;
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
        $this -> type = $type;
        $currentAuthType = $this -> auth -> hasRole(Auth\Role::ADMIN) ? self::TYPE_ADMIN : self::TYPE_PUBLISHER;

        if($currentAuthType == $type) {
            return;
        }

        if($currentAuthType == self::TYPE_ADMIN) {
            $this -> auth -> admin() -> removeRoleForUserByEmail($this -> email, Auth\Role::ADMIN);
        }
        else {
            $this -> auth -> admin() -> addRoleForUserByEmail($this -> email, Auth\Role::ADMIN);
        }
    }

    /**
     * Registers an user and returns its corresponding object.
     *
     * @param string $username The username.
     * @param string $email The email.
     * @param string $password The password.
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
        $adsky = AdSky::getInstance();
        $auth = $adsky -> getAuth();
        $id = $auth -> registerWithUniqueUsername($email, $password, $username, function($selector, $token) use ($adsky, $email, $confirm) {
            self::sendEmail($adsky -> getLanguageString('EMAIL_TITLE_CONFIRM'), $email, 'confirm.twig', [
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
     * @param string $title Email's title.
     * @param string $email Target's email.
     * @param string $template Template file.
     * @param array $additionalParameters Additional parameters (used by twig).
     */

    public static function sendEmail($title, $email, $template, $additionalParameters = []) {
        try {
            $websiteSettings = AdSky::getInstance() -> getWebsiteSettings();

            $renderer = new Renderer();
            $renderer -> addRelativePath('emails/');

            $sender = $websiteSettings -> getWebsiteEmail();
            $headers = 'From: ' . $sender . "\r\n";
            $headers .= 'Reply-To: ' . $sender . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $additionalParameters['url'] = $websiteSettings -> getWebsiteRoot();
            mail($email, $title, $renderer -> renderWithDefaultSettings($template, $additionalParameters), $headers);
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

    /**
     * Constructs an array from this user.
     *
     * @return array The array.
     */

    public function toArray() {
        return [
            'username' => $this -> username,
            'email' => $this -> email,
            'type' => $this -> type
        ];
    }

    public function __toString() {
        return json_encode($this -> toArray());
    }

}