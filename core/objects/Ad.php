<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../AdSky.php';
require_once __DIR__ . '/../Response.php';

require_once __DIR__ . '/User.php';

require_once __DIR__ . '/../Utils.php';

/**
 * Represents a SkyAd ad.
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

    public function __construct($username = null, $type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null) {
        $this -> _username = htmlspecialchars($username);
        $this -> _type = $type;
        $this -> _title = htmlspecialchars($title);
        $this -> _message = htmlspecialchars($message);
        $this -> _interval = $interval;
        $this -> _expiration = $expiration;
        $this -> _duration = $duration;
    }

    public function register() {
        try {
            $adsky = AdSky ::getInstance();

            if(self ::adExists($this -> _title)) {
                return new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
            }

            $adsky -> getMedoo() -> insert($adsky -> getMySQLSettings() -> getAdsTable(), [
                'title' => $this -> _title,
                'message' => $this -> _message,
                'username' => $this -> _username,
                'interval' => $this -> _interval,
                'until' => $this -> _expiration,
                'type' => $this -> _type,
                'duration' => $this -> _duration
            ]);

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public function update($type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null) {
        try {
            $adsky = AdSky::getInstance();

            if($title != null) {
                $title = htmlspecialchars($title);
            }

            if($title != ($this -> _title) && self ::adExists($title)) {
                return new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
            }

            $data = [];
            if($type != null) {
                if(!ctype_digit($type) || ($type != self::TYPE_TITLE && $type != self::TYPE_CHAT)) {
                    $type = self::TYPE_TITLE;
                }

                $this -> _type = $type;
                $data['type'] = $type;
            }

            if($title != null) {
                $this -> _title = $title;
                $data['title'] = $title;
            }

            if($message != null) {
                $message = htmlspecialchars($message);
                $this -> _message = $message;
                $data['message'] = $message;
            }

            if($interval != null) {
                if(!ctype_digit($interval) || $interval < 1) {
                    $interval = 1;
                }

                $this -> _interval = $interval;
                $data['interval'] = $interval;
            }

            if($expiration != null) {
                $min = mktime(0, 0, 0) + 60 * 60 * 24;
                if(!ctype_digit($expiration) || $expiration < $min) {
                    $expiration = $min;
                }

                $this -> _expiration = $expiration;
                $data['until'] = $expiration;
            }

            if($duration != null && $type == self::TYPE_TITLE) {
                if(!ctype_digit($duration) || $duration < 1) {
                    $duration = 1;
                }

                $this -> _duration = $duration;
                $data['duration'] = $duration;
            }

            if(!empty($data)) {
                $values = '';
                foreach(array_keys($data) as $key) {
                    $values .= ', `' . $key . '`=:' . $key;
                }

                $statement = $adsky -> getMedoo() -> update($adsky -> getMySQLSettings() -> getAdsTable(), $data, [
                    'type' => $this -> _type,
                    'title' => $this -> _title,
                    'username' => $this -> _username
                ]);

                if($statement -> rowCount() === 0) {
                    return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
                }
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public function delete() {
        try {
            $adsky = AdSky::getInstance();

            $statement = $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), [
                'type' => $this -> _type,
                'title' => $this -> _title,
                'username' => $this -> _username
            ]);

            if($statement -> rowCount() === 0) {
                return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public function renew($days) {
        try {
            $adsky = AdSky::getInstance();

            $statement = $adsky -> getMedoo() -> update($adsky -> getMySQLSettings() -> getAdsTable(), ['until[+]' => 60 * 60 * 24 * $days], [
                'type' => $this -> _type,
                'title' => $this -> _title
            ]);

            if($statement -> rowCount() === 0) {
                return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public static function validate($type, $title, $message, $interval, $expiration, $duration) {
        $adsky = AdSky::getInstance();
        $adSettings = $adsky -> getAdSettings();

        $type = intval($type);
        $duration = intval($duration);
        $interval = intval($interval);

        $expirationDate = new DateTime();
        $expirationDate -> setTimestamp(intval($expiration));
        $expirationDate -> setTime(0, 0, 0);

        $expiration = $expirationDate -> getTimestamp();

        $titleLength = strlen($title);
        $messageLength = strlen($message);

        if($title == null || $message == null) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_SET_TOOMANY'));
        }

        if($type != self::TYPE_TITLE && $type != self::TYPE_CHAT) {
            return new Response($adsky -> getLanguageString('API_ERROR_INVALID_TYPE'));
        }

        if(Ad::adExists($title)) {
            return new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
        }

        if($type == self::TYPE_TITLE) {
            if(!($adSettings -> getTitleAdTitleMinimumCharactersCount() <= $titleLength && $titleLength <= $adSettings -> getTitleAdTitleMaximumCharactersCount())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_TITLE_LENGTH'));
            }

            if(!($adSettings -> getTitleAdMessageMinimumCharactersCount() <= $messageLength && $messageLength <= $adSettings -> getTitleAdMessageMaximumCharactersCount())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_MESSAGE_LENGTH'));
            }

            if(!($adSettings -> getTitleAdMinimumSecondsToDisplay() <= $duration && $duration <= $adSettings -> getTitleAdMaximumSecondsToDisplay())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_DURATION'));
            }

            if(!($adSettings -> getTitleAdMinimumDisplayPerDay() <= $interval && $interval <= $adSettings -> getTitleAdMaximumDisplayPerDay())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_INTERVAL'));
            }

            $today = mktime(0, 0, 0);

            if(!($today + ($adSettings -> getTitleAdMinimumExpiration() * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($adSettings -> getTitleAdMaximumExpiration() * 60 * 60 * 24))) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EXPIRATIONDATE'));
            }
        }
        else {
            if(!($adSettings -> getChatAdTitleMinimumCharactersCount() <= $titleLength && $titleLength <= $adSettings -> getChatAdTitleMaximumCharactersCount())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_TITLE_LENGTH'));
            }

            if(!($adSettings -> getChatAdMessageMinimumCharactersCount() <= $messageLength && $messageLength <= $adSettings -> getChatAdMessageMaximumCharactersCount())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_MESSAGE_LENGTH'));
            }

            if(!($adSettings -> getChatAdMinimumDisplayPerDay() <= $interval && $interval <= $adSettings -> getChatAdMaximumDisplayPerDay())) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_INTERVAL'));
            }

            $today = mktime(0, 0, 0);
            if(!($today + ($adSettings -> getChatAdMinimumExpiration() * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($adSettings -> getChatAdMaximumExpiration() * 60 * 60 * 24))) {
                return new Response($adsky -> getLanguageString('API_ERROR_INVALID_EXPIRATIONDATE'));
            }
        }

        if($adSettings -> getAdPerDayLimit() > 0 && Ad::getNumberOfAdsPerDay() + $interval > $adSettings -> getAdPerDayLimit()) {
            return new Response($adsky -> getLanguageString('API_ERROR_LIMIT_REACHED'));
        }

        return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    }

    public static function adExists($title) {
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

    public static function getAds($page = null, $username = null) {
        $where = ['ORDER' => 'title'];
        if($username != null) {
            $where['username'] = $username;
        }

        $mySQLSettings = AdSky::getInstance() -> getMySQLSettings();
        return $mySQLSettings -> getPage($mySQLSettings -> getAdsTable(), '*', function($row) {
            return [
                'username' => $row['username'],
                'type' => intval($row['type']),
                'title' => $row['title'],
                'message' => $row['message'],
                'interval' => intval($row['interval']),
                'expiration' => intval($row['until']),
                'duration' => intval($row['duration'])
            ];
        }, $page, $where);
    }

    public static function deleteAdsFromUser($username) {
        $adsky = AdSky::getInstance();

        try {
            $adsky -> getMedoo() -> delete($adsky -> getMySQLSettings() -> getAdsTable(), ['username' => $username]);
            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public static function todayAds() {
        $adsky = AdSky ::getInstance();

        try {
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

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $object);
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public static function deleteExpired() {
        $adsky = AdSky ::getInstance();

        try {
            $adsky -> getPDO() -> query('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))') -> execute();
            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
        }
        catch(PDOException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'), null, $error);
        }
    }

    public static function getNumberOfAdsPerDay() {
        $adsky = AdSky::getInstance();

        try {
            return AdSky::getInstance() -> getMedoo() -> sum($adsky -> getMySQLSettings() -> getAdsTable(), 'interval', []);
        }
        catch(PDOException $error) {
            $adSettings = $adsky -> getAdSettings();
            return max($adSettings -> getTitleAdMaximumDisplayPerDay(), $adSettings -> getChatAdMaximumDisplayPerDay());
        }
    }

}