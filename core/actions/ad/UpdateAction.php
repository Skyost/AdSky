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
 * Action that allows to update an ad.
 */

class UpdateAction extends APIAction {

    private $id;
    private $type;
    private $title;
    private $message;
    private $interval;
    private $expiration;
    private $duration;

    /**
     * Creates a new UpdateAction instance.
     * 
     * @param int $id ID of the ad.
     * @param int $type New type of the ad.
     * @param string $title New title of the ad.
     * @param string $message New message of the ad.
     * @param int $interval New times to display ad per day.
     * @param int $expiration New expiration date (in timestamp).
     * @param int $duration New duration of a Title ad.
     */

    public function __construct($id, $type, $title, $message, $interval, $expiration, $duration = -1) {
        Autoloader::register();

        $this -> id = intval($id);
        $this -> type = intval($type);
        $this -> title = $title;
        $this -> message = $message;
        $this -> interval = intval($interval);
        $this -> expiration = intval($expiration);
        $this -> duration = intval($duration);
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // Throttle protection.
            $adsky -> getAuth() -> throttle([
                'ad-update',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);

            // We check if the current user is an admin.
            $user = $adsky -> getCurrentUserObject();
            if($user == null || !$user -> isAdmin()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            // We get the ID.
            if($this -> id == null) {
                return Response::notSet(['API_ERROR_NOT_SET_ID']);
            }

            // Now we can get our ad.
            $ad = Ad::getFromDatabase($this -> id);

            if($ad == null) {
                return new Response('API_ERROR_AD_NOT_FOUND');
            }

            // And we can update it.
            if($this -> type != null && !$ad -> setType($this -> type)) {
                return new Response('API_ERROR_INVALID_TYPE');
            }

            if($this -> title != null && !$ad -> setTitle($this -> title)) {
                return new Response('API_ERROR_INVALID_TITLE');
            }

            if($this -> message != null && !$ad -> setMessage($this -> message)) {
                return new Response('API_ERROR_INVALID_MESSAGE');
            }

            if($this -> interval != null && !$ad -> setInterval($this -> interval)) {
                return new Response('API_ERROR_INVALID_INTERVAL');
            }

            if($this -> expiration != null && !$ad -> setExpiration($this -> expiration)) {
                return new Response('API_ERROR_INVALID_EXPIRATIONDATE');
            }

            if($this -> duration != null && !$ad -> setDuration($this -> duration)) {
                return new Response('API_ERROR_INVALID_DURATION');
            }

            $ad -> sendUpdateToDatabase($this -> id);
            return new Response(null, 'API_SUCCESS');
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