<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../AdSky.php';

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

    public function __construct($username = null, $type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null) {
        $this -> _username = $username;
        $this -> _type = intval($type);
        $this -> _title = htmlspecialchars($title);
        $this -> _message = htmlspecialchars($message);
        $this -> _interval = intval($interval);
        $this -> _expiration = intval($expiration);
        $this -> _duration = intval($duration);
    }

    public function getUsername() {
        return $this -> _username;
    }

    public function setUsername($username) {
        $this -> _username = $username;
        return true;
    }

    public function getType() {
        return $this -> _type;
    }

    public function setType($type) {
        if($type != self::TYPE_TITLE && $type != self::TYPE_CHAT) {
            return false;
        }

        $this -> _type = intval($type);
        return true;
    }

    public function isTitleAd() {
        return $this -> _type == self::TYPE_TITLE;
    }

    public function isChatAd() {
        return !$this -> isTitleAd();
    }

    public function getTitle() {
        return $this -> _title;
    }

    public function setTitle($title) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateTitle($title, $this -> _type)) {
            return false;
        }

        $this -> _title = htmlspecialchars($title);
        return true;
    }

    public function getMessage() {
        return $this -> _message;
    }

    public function setMessage($message) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateMessage($message, $this -> _type)) {
            return false;
        }

        $this -> _message = htmlspecialchars($message);
        return true;
    }

    public function getInterval() {
        return $this -> _interval;
    }

    public function setInterval($interval = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateInterval($interval, $this -> _type)) {
            return false;
        }

        $this -> _interval = intval($interval);
        return true;
    }

    public function getExpiration() {
        return $this -> _expiration;
    }

    public function setExpiration($expiration = 0) {
        if(!AdSky::getInstance() -> getAdSettings() -> validateExpiration($expiration, $this -> _type)) {
            return false;
        }

        $this -> _expiration = intval($expiration);
        return true;
    }

    public function renew($days = 0) {
        return $this -> setExpiration($this -> _expiration + ($days * 24 * 60 * 60));
    }

    public function getDuration() {
        return $this -> _duration;
    }

    public function setDuration($duration = 0) {
        if($this -> isChatAd() || !AdSky::getInstance() -> getAdSettings() -> validateDuration($duration)) {
            return false;
        }

        $this -> _duration = intval($duration);
        return true;
    }

    public function setDeleted($isDeleted = true) {
        $this -> _isDeleted = $isDeleted;
    }

    public function sendUpdateToDatabase($id = 0) {
        $adsky = AdSky::getInstance();

        if($this -> _isDeleted) {
            $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['id' => $id]);
            return;
        }

        if(!self::adExists($id)) {
            if(!$adsky -> getAdSettings() -> validate($this -> _title, $this -> _message, $this -> _interval, $this -> _expiration, $this -> _duration, $this -> _type)) {
                return null;
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

    public static function adExists($id = 0) {
        return self::getFromDatabase($id) != null;
    }

    public static function getFromDatabase($id = 0) {
        $adsky = AdSky::getInstance();
        $rows = $adsky -> getMedoo() -> select($adsky -> getMySQLSettings() -> getAdsTable(), '*', ['id' => $id]);

        if(empty($rows)) {
            return null;
        }

        $row = $rows[0];
        return new Ad($row['username'], $row['type'], $row['title'], $row['message'], $row['interval'], $row['until'], $row['duration']);
    }

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