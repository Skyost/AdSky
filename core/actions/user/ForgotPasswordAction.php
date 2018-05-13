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
 * Action that allows to delete a user.
 */

class ForgotPasswordAction extends APIAction {

    private $email;

    /**
     * Creates a new DeleteAction instance.
     *
     * @param string $email Account's email.
     */

    public function __construct($email) {
        Autoloader::register();

        $this -> email = $email;
    }

    public function execute() {
        try {
            Autoloader::register();
            $adsky = AdSky::getInstance();

            // We check if an email has been sent.
            if($this -> email == null) {
                return Response::notSet(['API_ERROR_NOT_SET_EMAIL']);
            }

            // If it's okay, we can send the request.
            $adsky -> getAuth() -> forgotPassword($this -> email, function($selector, $token) use ($adsky) {
                User::sendEmail($adsky -> getLanguageString('EMAIL_TITLE_RESET'), $this -> email, 'reset.twig', [
                    'email' => $this -> email,
                    'selector' => $selector,
                    'token' => $token
                ]);
            });

            return new Response(null, 'API_SUCCESS');
        }
        catch(Auth\EmailNotVerifiedException $ex) {
            return new Response('API_ERROR_NOT_VERIFIED', null, $ex);
        }
        catch(Auth\InvalidEmailException $ex) {
            return new Response('API_ERROR_INVALID_EMAIL', null, $ex);
        }
        catch(Auth\ResetDisabledException $ex) {
            return new Response('API_ERROR_RESET_DISABLED', null, $ex);
        }
        catch(Auth\TooManyRequestsException $ex) {
            return new Response('API_ERROR_TOOMANYREQUESTS', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}