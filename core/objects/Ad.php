<?php

namespace AdSky\Core\Objects;

use AdSky\Core\AdSky;
use AdSky\Core\Autoloader;
use PDOException;

require_once __DIR__ . '/../Autoloader.php';

/**
 * Represents an ad.
 */

class Ad {

    const TYPE_TITLE = 0;
    const TYPE_CHAT = 1;

    private $username;
    private $type;
    private $title;
    private $message;
    private $interval;
    private $expiration;
    private $duration;

    private $isDeleted = false;

    /**
     * Creates a new ad instance.
     *
     * @param string $username Owner of the ad.
     * @param int $type Type of the ad.
     * @param string $title Title of the ad.
     * @param string $message Message of the ad.
     * @param int $interval Times to display ad per day.
     * @param int $expiration Expiration date (in timestamp).
     * @param int $duration Duration of a Title ad.
     */

    public function __construct($username = null, $type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null) {
        Autoloader::register();

        $this -> username = $username;
        $this -> type = intval($type);
        $this -> title = $title;
        $this -> message = $message;
        $this -> interval = intval($interval);
        $this -> expiration = intval($expiration);
        $this -> duration = intval($duration);
    }

    /**
     * Gets the owner of the ad.
     *
     * @return string The owner of the ad.
     */

    public function getUsername() {
        return $this -> username;
    }

    /**
     * Sets the owner of the ad.
     *
     * @param string $username The owner.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setUsername($username) {
        $this -> username = $username;
        return true;
    }

    /**
     * Gets the type of the ad.
     *
     * @return int The type of the ad.
     */

    public function getType() {
        return $this -> type;
    }

    /**
     * Sets the type of the ad.
     *
     * @param int $type The type.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setType($type) {
        if($type != self::TYPE_TITLE && $type != self::TYPE_CHAT) {
            return false;
        }

        $this -> type = intval($type);
        return true;
    }

    /**
     * Gets whether the ad is a Title ad.
     *
     * @return bool Whether the ad is a Title ad.
     */

    public function isTitleAd() {
        return $this -> type == self::TYPE_TITLE;
    }

    /**
     * Gets whether the ad is a Chat ad.
     *
     * @return bool Whether the ad is a Chat ad.
     */

    public function isChatAd() {
        return !$this -> isTitleAd();
    }

    /**
     * Gets the title of the ad.
     *
     * @return string The title of the ad.
     */

    public function getTitle() {
        return $this -> title;
    }

    /**
     * Sets the title of the ad.
     *
     * @param string $title The title.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setTitle($title) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateTitle($title, $this -> type)) {
            return false;
        }

        $this -> title = $title;
        return true;
    }

    /**
     * Gets the message of the ad.
     *
     * @return string The message of the ad.
     */

    public function getMessage() {
        return $this -> message;
    }

    /**
     * Sets the message of the ad.
     *
     * @param string $message The message.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setMessage($message) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateMessage($message, $this -> type)) {
            return false;
        }

        $this -> message = $message;
        return true;
    }

    /**
     * Gets the number of times to display this ad per day.
     *
     * @return int The number of times to display this ad per day.
     */

    public function getInterval() {
        return $this -> interval;
    }

    /**
     * Sets the number of times to display this ad per day.
     *
     * @param int $interval The number of times to display this ad per day.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setInterval($interval = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateInterval($interval, $this -> type)) {
            return false;
        }

        $this -> interval = intval($interval);
        return true;
    }

    /**
     * Gets the expiration date of the ad in timestamp.
     *
     * @return int The expiration date of the ad in timestamp.
     */

    public function getExpiration() {
        return $this -> expiration;
    }

    /**
     * Sets the expiration date of the ad.
     *
     * @param int $expiration The expiration date of the ad in timestamp.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setExpiration($expiration = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateExpiration($expiration, $this -> type)) {
            return false;
        }

        $this -> expiration = intval($expiration);
        return true;
    }

    /**
     * Renews this ad.
     *
     * @param int $days Number of days to add to the expiration date.
     *
     * @return bool Whether the operation has been successful.
     */

    public function renew($days = 0) {
        return $this -> setExpiration($this -> expiration + ($days * 24 * 60 * 60));
    }

    /**
     * Gets the duration of this Title ad.
     *
     * @return int The duration of this Title ad.
     */

    public function getDuration() {
        return $this -> duration;
    }

    /**
     * Sets the duration of this Title ad.
     *
     * @param int $duration The duration of this Title ad.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setDuration($duration = 0) {
        if($this -> isChatAd() || !AdSky::getInstance() -> getAdSettings() -> validateDuration($duration)) {
            return false;
        }

        $this -> duration = intval($duration);
        return true;
    }

    /**
     * Deletes (or undelete) this ad.
     *
     * @param bool $isDeleted Whether this ad should be deleted.
     */

    public function setDeleted($isDeleted = true) {
        $this -> isDeleted = $isDeleted;
    }

    /**
     * Saves this ad to the database.
     *
     * @param int $id Ad's ID. Can be ignored if the ad does not exist in the database.
     */

    public function sendUpdateToDatabase($id = 0) {
        $adsky = AdSky::getInstance();

        // If the ad has been deleted, then we trigger the delete on the database.
        if($this -> isDeleted) {
            $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['id' => $id]);
            return;
        }

        // Expiration column name is not available in MySQL so we have to replace it by something else.
        $data = $this -> toArray();
        $data['until'] = $data['expiration'];
        unset($data['expiration']);

        // If ads does not exist, then we have to insert it.
        if(!self::adExists($id)) {
            if(!$adsky -> getAdSettings() -> validate($this -> title, $this -> message, $this -> interval, $this -> expiration, $this -> duration, $this -> type)) {
                return;
            }

            $adsky -> getMedoo() -> insert($adsky -> getMySQLSettings() -> getAdsTable(), $data);
            return;
        }

        // Otherwise, we update it.
        $adsky -> getMedoo() -> update($adsky -> getMySQLSettings() -> getAdsTable(), $data, ['id' => $id]);
    }

    /**
     * Checks if the title exists in the database.
     *
     * @param string $title The title.
     *
     * @return bool Whether this title exists in the database.
     */

    public static function titleExists($title) {
        try {
            $adsky = AdSky::getInstance();
            return !empty($adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getAdsTable(), ['title'], [
                'title' => $title,
                'LIMIT' => 1
            ]));
        }
        catch(PDOException $error) {
            return false;
        }
    }

    /**
     * Checks if this ad exists in the database.
     *
     * @param int $id Ad's ID.
     *
     * @return bool Whether this ad exists in the database.
     */

    public static function adExists($id = 0) {
        return self::getFromDatabase($id) != null;
    }

    /**
     * Gets an ad from the database.
     *
     * @param int $id Ad's ID.
     *
     * @return Ad|null The ad.
     */

    public static function getFromDatabase($id = 0) {
        $adsky = AdSky::getInstance();
        $rows = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getAdsTable(), '*', ['id' => $id]);

        if(empty($rows)) {
            return null;
        }

        $row = $rows[0];
        return new Ad($row['username'], $row['type'], $row['title'], $row['message'], $row['interval'], $row['until'], $row['duration']);
    }

    /**
     * Constructs an array from this ad.
     *
     * @return array The array.
     */

    public function toArray() {
        return [
            'title' => $this -> title,
            'message' => $this -> message,
            'username' => $this -> username,
            'interval' => $this -> interval,
            'expiration' => $this -> expiration,
            'type' => $this -> type,
            'duration' => $this -> duration
        ];
    }

    public function __toString() {
        return json_encode($this -> toArray());
    }

}