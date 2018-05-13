<?php

namespace AdSky\Core\Actions\User;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to login a user.
 */

class LoginAction extends APIAction {

    private $email;
    private $password;
    private $rememberDuration;

    /**
     * Creates a new LoginAction instance.
     *
     * @param string $email Account's email.
     * @param string $password Account's password.
     * @param int $rememberDuration The remember duration.
     */

    public function __construct($email, $password, $rememberDuration) {
        Autoloader::register();

        $this -> email = $email;
        $this -> password = $password;
        $this -> rememberDuration = $rememberDuration;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We check if an email and a password have been sent.
            if($this -> email || $this -> password) {
                Response::notSet(['API_ERROR_NOT_SET_EMAIL', 'API_ERROR_NOT_SET_PASSWORD']);
            }

            // If yes, we check if the user is already logged in.
            if($adsky -> getCurrentUserObject() != null) {
                throw new Auth\AuthError();
            }

            // Else, we try to login him.
            $adsky -> getAuth() -> login($this -> email, $this -> password, $this -> rememberDuration);

            return new Response(null, 'API_SUCCESS');
        }
        catch(Auth\AttemptCancelledException $ex) {
            return new Response('API_ERROR_ATTEMPT_CANCELLED', null, $ex);
        }
        catch(Auth\EmailNotVerifiedException $ex) {
            return new Response('API_ERROR_NOT_VERIFIED', null, $ex);
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
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}