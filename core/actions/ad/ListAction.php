<?php

namespace AdSky\Core\Actions\Ad;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to list all ads.
 */

class ListAction extends APIAction {

    private $email;
    private $page;

    /**
     * Creates a new ListAction instance.
     *
     * @param string $email Email of the user to list ads.
     * @param int $page The page you want to request.
     */

    public function __construct($email = null, $page = null) {
        Autoloader::register();

        $this -> email = $email;
        $this -> page = $page;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We get the required page.
            if($this -> page == null || intval($this -> page) < 1) {
                $this -> page = 1;
            }

            // We check if the user is logged in.
            $user = $adsky -> getCurrentUserObject();
            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            // Username we want to list ads.
            if($user != null && $this -> email === 'current') {
                $this -> email = $user -> getEmail();
            }

            // Not we check if the user is a admin (or if the username corresponds to the current user).
            if($this -> email != $user -> getEmail() && !$user -> isAdmin()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            $where = ['ORDER' => 'title'];
            $data = [
                'ad-list',
                $_SERVER['REMOTE_ADDR'],
                $this -> page
            ];

            if($this -> email != null) {
                // We get its username by its email.
                $username = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getUsersTable(), 'username', ['email' => $this -> email]);
                if(empty($username)) {
                    throw new Auth\InvalidEmailException();
                }

                // And then we delete him.
                $username = $username[0];

                if($username != null) {
                    array_push($data, $username);
                    $where['username'] = $username;
                }
            }

            // Throttle protection.
            $user -> getAuth() -> throttle($data, 10, 60);

            // And let's show everything !
            $mySQLSettings = $adsky -> getMySQLSettings();
            return $mySQLSettings -> getPage($mySQLSettings -> getAdsTable(), '*', function($row) {
                return [
                    'id' => intval($row['id']),
                    'username' => $row['username'],
                    'type' => intval($row['type']),
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'interval' => intval($row['interval']),
                    'expiration' => intval($row['until']),
                    'duration' => intval($row['duration'])
                ];
            }, $this -> page, $where);
        }
        catch(Auth\TooManyRequestsException $ex) {
            return new Response('API_ERROR_TOOMANYREQUESTS', null, $ex);
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