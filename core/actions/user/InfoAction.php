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
 * Action that allows to get a user.
 */

class InfoAction extends APIAction {

    private $email;

    /**
     * Creates a new InfoAction instance.
     *
     * @param string $email Account's email.
     */

    public function __construct($email) {
        Autoloader::register();

        $this -> email = $email;
    }

    public function execute() {
        try {
            $adsky = AdSky ::getInstance();
            $user = $adsky -> getCurrentUserObject();

            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            $response = new Response(null, 'API_SUCCESS');
            if($this -> email !== 'current' && $this -> email != $user -> getEmail()) {
                if(!$user -> isAdmin()) {
                    return new Response('API_ERROR_NOT_ADMIN');
                }

                $target = new User($adsky -> getAuth(), $this -> email, null, null);
                $target -> loginAsUserIfNeeded();

                $response -> setObject($target -> toArray());
                $user -> loginAsUserIfNeeded();
            }
            else {
                $response -> setObject($user -> toArray());
            }

            return $response;
        }
        catch(Auth\EmailNotVerifiedException $ex) {
            return new Response('API_ERROR_NOT_VERIFIED', null, $ex);
        }
        catch(Auth\InvalidEmailException $ex) {
            return new Response('API_ERROR_INVALID_EMAIL', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}