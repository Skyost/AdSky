<?php

namespace AdSky\Core\Actions\Update;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Objects\GithubUpdater;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to check for updates.
 */

class CheckAction extends APIAction {
    
    /**
     * Creates a new CheckAction instance.
     */

    public function __construct() {
        Autoloader::register();
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We check if the current user is an admin.
            $user = $adsky -> getCurrentUserObject();
            if($user == null || !$user -> isAdmin()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            // Throttle protection.
            $auth = $adsky -> getAuth();
            $auth -> throttle([
                'update-check',
                $_SERVER['REMOTE_ADDR']
            ], 5, 60);

            // Then we check for updates.
            $updater = new GithubUpdater();
            $result = $updater -> check();

            $response = new Response(null, 'API_SUCCESS');

            if($result != null) {
                $response -> setObject($result);
            }

            return $response;
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