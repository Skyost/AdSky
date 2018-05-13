<?php

namespace AdSky\Core\Actions\Ad;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\Ad;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to get an ad.
 */

class InfoAction extends APIAction {

    private $id;

    /**
     * Creates a new InfoAction instance.
     *
     * @param int $id Ad's ID.
     */

    public function __construct($id) {
        Autoloader::register();

        $this -> id = $id;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // Throttle protection.
            $adsky -> getAuth() -> throttle([
                'ad-info',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);

            // We get the ID.
            if($this -> id == null) {
                return Response::notSet(['API_ERROR_NOT_SET_ID']);
            }

            // Now we can get our ad.
            $ad = Ad::getFromDatabase($this -> id);

            if($ad == null) {
                return new Response('API_ERROR_AD_NOT_FOUND');
            }

            // We check if the current user is an admin.
            $user = $adsky -> getCurrentUserObject();
            if($user == null || ($ad -> getUsername() != $user -> getUsername() && !$user -> isAdmin())) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            return new Response(null, 'API_SUCCESS', $ad -> toArray());
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