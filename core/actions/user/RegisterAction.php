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
 * Action that allows to register a user.
 */

class RegisterAction extends APIAction {

    private $username;
    private $email;
    private $password;

    /**
     * Creates a new RegisterAction instance.
     *
     * @param string $username Account's username.
     * @param string $email Account's email.
     * @param string $password Account's password.
     */

    public function __construct($username, $email, $password) {
        Autoloader::register();

        $this -> username = $username;
        $this -> email = $email;
        $this -> password = $password;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We check if all required parameters have been sent.
            if($this -> username == null || $this -> email == null || $this -> password == null) {
                return Response::notSet(['API_ERROR_NOT_SET_USERNAME', 'API_ERROR_NOT_SET_EMAIL', 'API_ERROR_NOT_SET_PASSWORD']);
            }

            // We also check if the user is not logged in.
            if($adsky -> getCurrentUserObject() != null) {
                throw new Auth\AuthError();
            }

            // Now that we are sure, we register the user.
            $user = User::register($this -> username, $this -> email, $this -> password);
            return new Response(null, 'API_SUCCESS', $user -> toArray());
        }
        catch(Auth\DuplicateUsernameException $ex) {
            return new Response('API_ERROR_USERNAME_ALREADYEXISTS', null, $ex);
        }
        catch(Auth\InvalidEmailException $ex) {
            return new Response('API_ERROR_INVALID_EMAIL', null, $ex);
        }
        catch(Auth\InvalidPasswordException $ex) {
            return new Response('API_ERROR_INVALID_PASSWORD', null, $ex);
        }
        catch(Auth\TooManyRequestsException $ex) {
            return new Response('API_ERROR_TOOMANYREQUESTS', null, $ex);
        }
        catch(Auth\UnknownIdException $ex) {
            return new Response('API_ERROR_UNKNOWN_ID', null, $ex);
        }
        catch(Auth\UserAlreadyExistsException $ex) {
            return new Response('API_ERROR_EMAIL_ALREADYEXISTS', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}