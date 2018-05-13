<?php

namespace AdSky\Core\Actions\User;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to logout a user.
 */

class LogoutAction extends APIAction {
    
    /**
     * Creates a new LogoutAction instance.
     */

    public function __construct() {
        Autoloader::register();
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We check if the current user is logged in.
            $user = $adsky -> getCurrentUserObject();
            if($user == null) {
                throw new Auth\AuthError();
            }

            // If yes, let's logout !
            $user -> getAuth() -> logOut();
            return new Response(null, 'API_SUCCESS');
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}