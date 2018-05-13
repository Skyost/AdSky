<?php

namespace AdSky\Core\Objects;

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use AdSky\Core\Utils;
use ZipArchive;

require_once __DIR__ . '/../Autoloader.php';

/**
 * A Github update check and downloader.
 */

class GithubUpdater {

    const UPDATES_FOLDER = '../../updates/';

    private $repo;

    /**
     * Creates a new GithubUpdater instance.
     *
     * @param string $repo The Github repo.
     */

    function __construct($repo = 'AdSky') {
        Autoloader::register();

        $this -> repo = $repo;
    }

    /**
     * Checks if an update is available.
     *
     * @return array|null An array containing the version and the download link.
     */

    public function check() {
        $response = self::githubApiRequest('https://api.github.com/repos/Skyost/' . $this -> repo . '/releases/latest');
        if(empty($response)) {
            return null;
        }

        if(version_compare(substr(AdSky::APP_VERSION, 1), substr($response['tag_name'], 1)) >= 0) {
            return null;
        }

        return [
            'version' => $response['tag_name'],
            'download' => $response['assets'][0]['browser_download_url']
        ];
    }

    /**
     * Downloads the update.
     *
     * @return bool Whether the download is a success.
     */

    public function download() {
        $response = $this -> check();
        if($response == null) {
            return false;
        }

        if(!is_dir(self::UPDATES_FOLDER)) {
            mkdir(self::UPDATES_FOLDER);
        }

        $zip = self::githubApiRequest($response['download'], false);
        $destination = fopen(self::UPDATES_FOLDER . 'update.zip', 'w');

        return fwrite($destination, $zip) && fclose($destination);
    }

    /**
     * Updates AdSky.
     *
     * @return bool Whether the update is a success.
     */

    public function update() {
        if(!$this -> download()) {
            return false;
        }

        $zip = new ZipArchive();
        if(!$zip -> open(self::UPDATES_FOLDER . 'update.zip')) {
            return false;
        }

        $zip -> extractTo(self::UPDATES_FOLDER);
        $zip -> close();

        unlink(self::UPDATES_FOLDER . 'update.zip');

        if(!file_exists(self::UPDATES_FOLDER . 'upgrade.php')) {
            return false;
        }

        include self::UPDATES_FOLDER . 'upgrade.php';

        if(!Utils::delTree(self::UPDATES_FOLDER)) {
            return false;
        }

        return true;
    }

    /**
     * Sends a request to the Github API.
     *
     * @param String $method The API method.
     * @param bool $json Whether the content should be JSON decoded.
     *
     * @return mixed The JSON object.
     */

    public static function githubApiRequest($method, $json = true) {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: ' . AdSky::APP_NAME . ' ' . AdSky::APP_VERSION
                ]
            ]
        ];

        $content = @file_get_contents($method, false, stream_context_create($options));
        if($content === false) {
            return [];
        }

        if($json) {
            return json_decode($content, true);
        }

        return $content;
    }

}