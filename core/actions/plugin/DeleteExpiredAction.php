<?php

namespace AdSky\Core\Actions\Plugin;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to delete expired ads.
 */

class DeleteExpiredAction extends APIAction {

    private $key;

    /**
     * Creates a new DeleteExpiredAction instance.
     *
     * @param string $key Plugin key.
     */

    public function __construct($key) {
        Autoloader::register();

        $this -> key = $key;
    }

    public function execute() {
        try {
            $adsky = AdSky::getInstance();

            // We try to validate the sent key.
            if($this -> key == null || $this -> key != $adsky -> getPluginSettings() -> getPluginKey()) {
                return new Response('API_ERROR_INVALID_PLUGIN_KEY');
            }

            // If everything is okay, we can query our database.
            $adsky -> getPDO() -> query('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))') -> execute();
            return new Response(null, 'API_SUCCESS');
        }
        catch(\PDOException $ex) {
            return new Response('API_ERROR_INVALID_PLUGIN_KEY', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}