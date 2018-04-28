<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../AdSky.php';

/**
 * Represents an ad.
 */

class Ad {

    const TYPE_TITLE = 0;
    const TYPE_CHAT = 1;

    private $_username;
    private $_type;
    private $_title;
    private $_message;
    private $_interval;
    private $_expiration;
    private $_duration;

    private $_isDeleted = false;

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
        $this -> _username = $username;
        $this -> _type = intval($type);
        $this -> _title = htmlspecialchars($title);
        $this -> _message = htmlspecialchars($message);
        $this -> _interval = intval($interval);
        $this -> _expiration = intval($expiration);
        $this -> _duration = intval($duration);
    }

    /**
     * Gets the owner of the ad.
     *
     * @return string The owner of the ad.
     */

    public function getUsername() {
        return $this -> _username;
    }

    /**
     * Sets the owner of the ad.
     *
     * @param string $username The owner.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setUsername($username) {
        $this -> _username = $username;
        return true;
    }

    /**
     * Gets the type of the ad.
     *
     * @return int The type of the ad.
     */

    public function getType() {
        return $this -> _type;
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

        $this -> _type = intval($type);
        return true;
    }

    /**
     * Gets whether the ad is a Title ad.
     *
     * @return bool Whether the ad is a Title ad.
     */

    public function isTitleAd() {
        return $this -> _type == self::TYPE_TITLE;
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
        return $this -> _title;
    }

    /**
     * Sets the title of the ad.
     *
     * @param string $title The title.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setTitle($title) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateTitle($title, $this -> _type)) {
            return false;
        }

        $this -> _title = htmlspecialchars($title);
        return true;
    }

    /**
     * Gets the message of the ad.
     *
     * @return string The message of the ad.
     */

    public function getMessage() {
        return $this -> _message;
    }

    /**
     * Sets the message of the ad.
     *
     * @param string $message The message.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setMessage($message) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateMessage($message, $this -> _type)) {
            return false;
        }

        $this -> _message = htmlspecialchars($message);
        return true;
    }

    /**
     * Gets the number of times to display this ad per day.
     *
     * @return int The number of times to display this ad per day.
     */

    public function getInterval() {
        return $this -> _interval;
    }

    /**
     * Sets the number of times to display this ad per day.
     *
     * @param int $interval The number of times to display this ad per day.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setInterval($interval = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateInterval($interval, $this -> _type)) {
            return false;
        }

        $this -> _interval = intval($interval);
        return true;
    }

    /**
     * Gets the expiration date of the ad in timestamp.
     *
     * @return int The expiration date of the ad in timestamp.
     */

    public function getExpiration() {
        return $this -> _expiration;
    }

    /**
     * Sets the expiration date of the ad.
     *
     * @param int $expiration The expiration date of the ad in timestamp.
     *
     * @return bool Whether the operation has been successful.
     */

    public function setExpiration($expiration = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateExpiration($expiration, $this -> _type)) {
            return false;
        }

        $this -> _expiration = intval($expiration);
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
        return $this -> setExpiration($this -> _expiration + ($days * 24 * 60 * 60));
    }

    /**
     * Gets the duration of this Title ad.
     *
     * @return int The duration of this Title ad.
     */

    public function getDuration() {
        return $this -> _duration;
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

        $this -> _duration = intval($duration);
        return true;
    }

    /**
     * Deletes (or undelete) this ad.
     *
     * @param bool $isDeleted Whether this ad should be deleted.
     */

    public function setDeleted($isDeleted = true) {
        $this -> _isDeleted = $isDeleted;
    }

    /**
     * Saves this ad to the database.
     *
     * @param int $id Ad's ID. Can be ignored if the ad does not exist in the database.
     */

    public function sendUpdateToDatabase($id = 0) {
        $adsky = AdSky::getInstance();

        if($this -> _isDeleted) {
            $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['id' => $id]);
            return;
        }

        if(!self::adExists($id)) {
            if(!$adsky -> getAdSettings() -> validate($this -> _title, $this -> _message, $this -> _interval, $this -> _expiration, $this -> _duration, $this -> _type)) {
                return;
            }

            $adsky -> getMedoo() -> insert($adsky -> getMySQLSettings() -> getAdsTable(), [
                'title' => htmlspecialchars($this -> _title),
                'message' => htmlspecialchars($this -> _message),
                'username' => $this -> _username,
                'interval' => intval($this -> _interval),
                'until' => intval($this -> _expiration),
                'type' => intval($this -> _type),
                'duration' => intval($this -> _duration)
            ]);

            return;
        }

        $adsky -> getMedoo() -> update($adsky -> getMySQLSettings() -> getAdsTable(), [
            'title' => $this -> _title,
            'message' => $this -> _message,
            'username' => $this -> _username,
            'interval' => $this -> _interval,
            'until' => $this -> _expiration,
            'type' => $this -> _type,
            'duration' => $this -> _duration
        ], ['id' => $id]);
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
            'title' => $this -> _title,
            'message' => $this -> _message,
            'username' => $this -> _username,
            'interval' => $this -> _interval,
            'expiration' => $this -> _expiration,
            'type' => $this -> _type,
            'duration' => $this -> _duration
        ];
    }

    public function __toString() {
        return json_encode($this -> toArray());
    }

}