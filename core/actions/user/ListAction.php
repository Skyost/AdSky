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
 * Action that allows to list all users.
 */

class ListAction extends APIAction {

    private $page;

    /**
     * Creates a new ListAction instance.
     *
     * @param int $page The page you want to request.
     */

    public function __construct($page = null) {
        Autoloader::register();

        $this -> page = $page;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We get the required page.
            if($this -> page == null || intval($this -> page) < 1) {
                $this -> page = 1;
            }

            $user = $adsky -> getCurrentUserObject();

            // We check if the current user is an admin.
            if($user == null || !$user -> isAdmin()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            // Throttle protection.
            $user -> getAuth() -> throttle([
                'user-list',
                $_SERVER['REMOTE_ADDR'],
                $this -> page
            ], 10, 60);

            // And let's show everything !
            $mySQLSettings = $adsky -> getMySQLSettings();
            return $mySQLSettings -> getPage($mySQLSettings -> getUsersTable(), '*', function($row) {
                return [
                    'username' => $row['username'],
                    'email' => $row['email'],
                    'type' => $row['roles_mask'] & Auth\Role::ADMIN === Auth\Role::ADMIN ? User::TYPE_ADMIN : User::TYPE_PUBLISHER,
                    'verified' => $row['verified'],
                    'last_login' => intval($row['last_login']),
                    'registered' => intval($row['registered'])
                ];
            }, $this -> page, ['ORDER' => 'last_login']);
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