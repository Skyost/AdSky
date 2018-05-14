<?php

namespace AdSky\Core\Actions\Plugin;

use AdSky\Core\Actions\APIAction;
use AdSky\Core\Actions\Response;
use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;

require_once __DIR__ . '/../../../core/Autoloader.php';

/**
 * Action that allows to get today ads.
 */

class TodayAdsAction extends APIAction {

    private $key;

    /**
     * Creates a new TodayAdsAction instance.
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
            $result = $adsky -> getPDO() -> query('SELECT * FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` > UNIX_TIMESTAMP(CONVERT_TZ(DATE(SUBDATE(NOW(), 1)), \'+00:00\', \'SYSTEM\'))');
            $object = [];

            foreach(($result -> fetchAll()) as $row) {
                array_push($object, [
                    'username' => $row['username'],
                    'type' => intval($row['type']),
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'interval' => intval($row['interval']),
                    'expiration' => intval($row['until']),
                    'duration' => intval($row['duration'])
                ]);
            }

            return new Response(null, 'API_SUCCESS', $object);
        }
        catch(\PDOException $ex) {
            return new Response('API_ERROR_MYSQL_ERROR', null, $ex);
        }
        catch(\Exception $ex) {
            return new Response('API_ERROR_GENERIC_ERROR', null, $ex);
        }
    }

}