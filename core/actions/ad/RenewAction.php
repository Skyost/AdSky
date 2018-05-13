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
 * Action that allows to renew an ad.
 */

class RenewAction extends APIAction {

    private $id;
    private $days;

    /**
     * Creates a new RenewAction instance.
     *
     * @param int $id Ad's ID.
     * @param int $days Days to add to the ad.
     */

    public function __construct($id, $days) {
        Autoloader::register();

        $this -> id = $id;
        $this -> days = $days;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We check if all arguments are okay.
            if($this -> id == null || $this -> days == null) {
                return Response::notSet(['API_ERROR_NOT_SET_ID', 'API_ERROR_NOT_SET_DAYS']);
            }

            if($this -> days <= 0) {
                return new Response('API_ERROR_INVALID_RENEWDAY');
            }

            // Throttle protection.
            $auth = $adsky -> getAuth();
            $auth -> throttle([
                'ad-renew',
                $_SERVER['REMOTE_ADDR']
            ], 5, 60);

            // We check if the user is logged in.
            $user = $adsky -> getCurrentUserObject();
            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            // Okay, now we can select our ad.
            $ad = Ad::getFromDatabase($this -> id);
            if($ad == null) {
                return new Response('API_ERROR_AD_NOT_FOUND');
            }

            // We check if the days parameter is good.
            if(!$ad -> renew($this -> days)) {
                return new Response('API_ERROR_INVALID_RENEWDAY');
            }

            // If the user is an admin, we don't have to use the PayPal API.
            $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();
            if($user -> isAdmin()) {
                $ad -> sendUpdateToDatabase($this -> id);
                return new Response(null, 'API_SUCCESS', $root . 'admin/?message=renew_success#list');
            }

            // We check if this is the correct user.
            if($ad -> getUsername() != $user -> getUsername()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            // Otherwise, let's create a payment !
            $url = $root . 'payment/renew/?' . http_build_query([
                'id' => $this -> id,
                'days' => $this -> days
            ]);
            return new Response(null, 'API_SUCCESS', $adsky -> getPayPalSettings() -> createApprovalLink($url, $ad -> getType(), $ad -> getInterval(), $this -> days));
        }
        catch(Auth\TooManyRequestsException $ex) {
            return new Response('API_ERROR_TOOMANYREQUESTS', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\PDOException $ex) {
            return new Response('API_ERROR_MYSQL_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_PAYPAL_REQUEST', null, $ex);
        }
    }

}