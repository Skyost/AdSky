<?php

namespace AdSky\Core\Actions\User;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\User;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to update a user.
 */

class UpdateAction extends APIAction {

    private $oldEmail;
    private $oldPassword;
    private $email;
    private $password;
    private $type;
    private $force;

    /**
     * Creates a new UpdateAction instance.
     *
     * @param string $oldEmail "Old" account's email.
     * @param string $oldPassword "Old" account's password.
     * @param string $email New account's email.
     * @param string $password New account's password.
     * @param string $type New account's type.
     * @param string $force Force update the account (allows to not enter the the old password and to change the type). If set to true, you must specify the "oldemail" parameter to identify the target account. Only admins can do that.
     */

    public function __construct($oldEmail, $oldPassword, $email, $password, $type, $force) {
        Autoloader::register();

        $this -> oldEmail = $oldEmail;
        $this -> oldPassword = $oldPassword;
        $this -> email = $email;
        $this -> password = $password;
        $this -> type = $type;
        $this -> force = $force;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            $auth = $adsky -> getAuth();
            $user = $adsky -> getCurrentUserObject();

            // We check if the current user is logged in.
            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            // We also have to check if the force parameter is set to true.
            if($this -> force == true) {
                // The user must be an admin to use the force mode.
                if(!$user -> isAdmin()) {
                    return new Response('API_ERROR_NOT_ADMIN');
                }

                // We check if a valid type has been sent.
                if($this -> type != null && ($this -> type != User::TYPE_ADMIN && $this -> type != User::TYPE_PUBLISHER)) {
                    return new Response('API_ERROR_INVALID_TYPE');
                }

                // We prepare our target.
                $target = new User($auth, $this -> oldEmail == null || $this -> oldEmail === 'current' ? $auth -> getEmail() : $this -> oldEmail, null, null);

                // We impersonate the user if needed.
                $target -> loginAsUserIfNeeded();

                // Now we can edit everything.
                if($this -> email != null) {
                    $target -> setEmail($this -> email, false);
                }

                if($this -> type != null) {
                    $target -> setType($this -> type);
                }

                if($this -> password != null) {
                    $auth -> changePasswordWithoutOldPassword($this -> password);
                }

                // We login back our admin.
                $user -> loginAsUserIfNeeded();
                return new Response(null, 'API_SUCCESS');
            }

            // If we are not in force mode, we must have an old password.
            if($this -> oldPassword == null) {
                return Response::notSet(['API_ERROR_NOT_SET_OLDPASSWORD']);
            }

            // We check if the old password is okay.
            if(!$auth -> reconfirmPassword($this -> oldPassword)) {
                throw new Auth\InvalidPasswordException();
            }

            // If yes, we can edit everything.
            if($this -> email != null) {
                $user -> setEmail($this -> email);
            }

            if($this -> password != null) {
                $auth -> changePassword($this -> oldPassword, $this -> password);
            }

            return new Response(null, 'API_SUCCESS');
        }
        catch(Auth\EmailNotVerifiedException $ex) {
            return new Response('API_ERROR_NOT_VERIFIED', null, $ex);
        }
        catch(Auth\InvalidEmailException $ex) {
            return new Response('API_ERROR_INVALID_EMAIL', null, $ex);
        }
        catch(Auth\NotLoggedInException $ex) {
            return new Response('API_ERROR_NOT_LOGGEDIN', null, $ex);
        }
        catch(Auth\TooManyRequestsException $ex) {
            return new Response('API_ERROR_TOOMANYREQUESTS', null, $ex);
        }
        catch(Auth\UserAlreadyExistsException $ex) {
            return new Response('API_ERROR_EMAIL_ALREADYEXISTS', null, $ex);
        }
        catch(Auth\InvalidPasswordException $ex) {
            return new Response('API_ERROR_INVALID_PASSWORD', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}