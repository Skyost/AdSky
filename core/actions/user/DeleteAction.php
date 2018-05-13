<?php

namespace AdSky\Core\Actions\User;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use Delight\Auth;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to delete a user.
 */

class DeleteAction extends APIAction {

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

            $user = $adsky -> getCurrentUserObject();

            // We check if the current user is logged in.
            if($user == null) {
                return new Response('API_ERROR_NOT_LOGGEDIN');
            }

            // If it's okay, we can check which user we want to delete.
            $auth = $user -> getAuth();
            $email = $this -> email == null || $this -> email === 'current' ? $auth -> getEmail() : $this -> email;
            if($email != $user -> getEmail() && !$user -> isAdmin()) {
                return new Response('API_ERROR_NOT_ADMIN');
            }

            // We get its username by its email.
            $username = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getUsersTable(), 'username', ['email' => $email]);
            if(empty($username)) {
                throw new Auth\InvalidEmailException();
            }

            // And then we delete him.
            $username = $username[0];
            $auth -> admin() -> deleteUserByUsername($username);

            $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);

            return new Response(null, 'API_SUCCESS');
        }
        catch(Auth\InvalidEmailException $ex) {
            return new Response('API_ERROR_INVALID_EMAIL', null, $ex);
        }
        catch(\PDOException $ex) {
            return new Response('API_ERROR_MYSQL_ERROR', null, $ex);
        }
        catch(Auth\AuthError $ex) {
            return new Response('API_ERROR_GENERIC_AUTH_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}