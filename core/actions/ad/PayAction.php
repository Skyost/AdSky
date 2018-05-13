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
 * Action that allows to pay for an ad.
 */

class PayAction extends APIAction {

    private $type;
    private $title;
    private $message;
    private $interval;
    private $expiration;
    private $duration;

    /**
     * Creates a new PayAction instance.
     * 
     * @param int $type Type of the ad.
     * @param string $title Title of the ad.
     * @param string $message Message of the ad.
     * @param int $interval Times to display ad per day.
     * @param int $expiration Expiration date (in timestamp).
     * @param int $duration Duration of a Title ad.
     */

    public function __construct($type, $title, $message, $interval, $expiration, $duration = -1) {
        Autoloader::register();

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

            $auth = $adsky -> getAuth();

            // Throttle protection.
            $auth -> throttle([
                'ad-pay',
                $_SERVER['REMOTE_ADDR']
            ], 5, 60);

            // We check if the user is logged in.
            $user = $adsky -> getCurrentUserObject();
            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            // We check if the ad is okay.
            if($this -> type != null && ($this -> type !== Ad::TYPE_TITLE && $this -> type !== Ad::TYPE_CHAT)) {
                return new Response('API_ERROR_INVALID_TYPE');
            }

            $adSettings = $adsky -> getAdSettings();
            if(!$adSettings -> validateTitle($this -> title, $this -> type)) {
                return new Response('API_ERROR_INVALID_TITLE');
            }

            if(!$adSettings -> validateMessage($this -> message, $this -> type)) {
                return new Response('API_ERROR_INVALID_MESSAGE');
            }

            if(!$adSettings -> validateInterval($this -> interval, $this -> type)) {
                return new Response('API_ERROR_INVALID_INTERVAL');
            }

            if(!$adSettings -> validateExpiration($this -> expiration, $this -> type)) {
                return new Response('API_ERROR_INVALID_EXPIRATIONDATE');
            }

            if($this -> type === Ad::TYPE_TITLE && !$adSettings -> validateDuration($this -> duration)) {
                return new Response('API_ERROR_INVALID_DURATION');
            }

            if(Ad::titleExists($this -> title)) {
                return new Response('API_ERROR_SAME_NAME');
            }

            // So now, we are going to create the ad.
            $root = $adsky -> getWebsiteSettings() -> getWebsiteRoot();

            $numberOfAdsPerDay = $adsky -> getMedoo() -> sum($adsky -> getMySQLSettings() -> getAdsTable(), 'interval', []);
            if($adSettings -> getAdPerDayLimit() > 0 && $numberOfAdsPerDay + $this -> interval > $adSettings -> getAdPerDayLimit()) {
                return new Response('API_ERROR_LIMIT_REACHED');
            }

            // If the user is an admin, we don't have to use the PayPal API.
            if($user -> isAdmin()) {
                $ad = new Ad($user -> getUsername(), $this -> type, $this -> title, $this -> message, $this -> interval, $this -> expiration, $this -> duration);
                $ad -> sendUpdateToDatabase();

                return new Response(null, 'API_SUCCESS', $root . 'admin/?message=create_success#create');
            }

            // Otherwise, let's create a payment !
            $url = $root . 'payment/register/?' . http_build_query([
                'title' => $this -> title,
                'message' => $this -> message,
                'interval' => $this -> interval,
                'expiration' => $this -> expiration,
                'type' => $this -> type,
                'duration' => $this -> duration
            ]);
            $totalDays = ($this -> expiration - gmmktime(0, 0, 0)) / (60 * 60 * 24);

            return new Response(null, 'API_SUCCESS', $adsky -> getPayPalSettings() -> createApprovalLink($url, $this -> type, $this -> interval, $totalDays));
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