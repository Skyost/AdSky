<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../Response.php';

use Delight\Auth;
use PayPal\Api\Amount;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

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
        $this -> _username = $username;
        $this -> _type = $type;
        $this -> _title = $title;
        $this -> _message = $message;
        $this -> _interval = $interval;
        $this -> _expiration = $expiration;
        $this -> _duration = $duration;
    }

    public function register() {
        $adsky = AdSky::getInstance();
        $pdo = $adsky -> getPDO();

        if(self::adExists($this -> _title)) {
            return new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
        }

        $statement = $pdo -> prepare('INSERT INTO `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` VALUES (NULL, :title, :message, :username, :interval, :until, :type, :duration)');
        if($statement -> execute([
            'title' => htmlspecialchars($this -> _title),
            'message' => htmlspecialchars($this -> _message),
            'username' => htmlspecialchars($this -> _username),
            'interval' => $this -> _interval,
            'until' => $this -> _expiration,
            'type' => $this -> _type,
            'duration' => $this -> _duration
        ])) {
            return new Response(null,  $adsky -> getLanguageString('API_SUCCESS'));
        }

        return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public function update($type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null) {
        $adsky = AdSky::getInstance();
        $pdo = $adsky -> getPDO();

        try {
            $auth = $adsky -> getAuth();
            $auth -> throttle([
                'ad-update',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }

        if($title != null) {
            $title = htmlspecialchars($title);
        }

        if($title != ($this -> _title) && self::adExists($title)) {
            return new Response($adsky -> getLanguageString('API_ERROR_SAME_NAME'));
        }

        $where = ' WHERE `type`=' . $this -> _type . ' AND `title`=\'' . $this -> _title . '\' AND `username`=\'' . $this -> _username . '\'';
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

        $result = true;
        if($data != []) {
            $values = '';
            foreach(array_keys($data) as $key) {
                $values .= ', `' . $key . '`=:' . $key;
            }

            $statement = $pdo -> prepare('UPDATE `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` SET' . substr($values, 1) . $where);
            $result = $statement -> execute($data);

            if($statement -> rowCount() === 0) {
                return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
            }
        }

        return $result ? new Response(null, $adsky -> getLanguageString('API_SUCCESS')) : new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public function delete() {
        $adsky = AdSky::getInstance();
        $pdo = $adsky -> getPDO();

        try {
            $auth = $adsky -> getAuth();
            $auth -> throttle([
                'ad-delete',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }

        $statement = $pdo -> prepare('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `type`=:type AND `title`=:title AND `username`=:username');
        $result = $statement -> execute([
            'type' => $this -> _type,
            'title' => $this -> _title,
            'username' => $this -> _username
        ]);

        if($statement -> rowCount() === 0) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
        }

        return $result ? new Response(null, $adsky -> getLanguageString('API_SUCCESS')) : new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public function renew($days) {
        $adsky = AdSky::getInstance();

        $statement = $adsky -> getPDO() -> prepare('UPDATE `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` SET `until` = `until` + :days WHERE `type`=:type AND `title`=:title');
        $result = $statement -> execute([
            'days' => 60 * 60 * 24 * $days,
            'type' => $this -> _type,
            'title' => $this -> _title
        ]);

        if($statement -> rowCount() === 0) {
            return new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'));
        }

        return $result ? new Response(null, $adsky -> getLanguageString('API_SUCCESS')) : new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public function createPayPalTransaction($totalDays = null) {
        $adsky = AdSky::getInstance();

        if($totalDays == null) {
            $totalDays = (intval($this -> _expiration) - mktime(0, 0, 0)) / (60 * 60 * 24);
        }

        $amount = new Amount();
        $amount -> setTotal(($this -> _type == self::TYPE_TITLE ? $adsky -> getAdSettings() -> getTitleAdCost() : $adsky -> getAdSettings() -> getChatAdCost()) * intval($this -> _interval) * $totalDays);
        $amount -> setCurrency($adsky -> getPayPalSettings() -> getPayPalCurrency());

        $transaction = new Transaction();
        $transaction
            -> setAmount($amount)
            -> setDescription(sprintf($adsky -> getLanguageString('API_PAYPAL_ITEM'), $this -> _interval, ($this -> _type == self::TYPE_TITLE ? $adsky -> getLanguageString('AD_TYPE_TITLE') : $adsky -> getLanguageString('AD_TYPE_CHAT')), $totalDays));

        return $transaction;
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
        $adsky = AdSky::getInstance();

        $statement = $adsky -> getPDO() -> prepare('SELECT `title` FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `title`=:title LIMIT 1');
        $statement -> execute(['title' => $title]);
        return $statement -> fetch() != false;
    }

    public static function getAds($page = null, $username = null) {
        $adsky = AdSky::getInstance();

        try {
            $auth = $adsky -> getAuth();
            $auth -> throttle([
                'ad-get',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error);
        }

        if($page == null || $page < 1) {
            $page = 1;
        }
        $page = intval($page);

        $where = ' ORDER BY `title`';
        if($username != null) {
            $where = ' WHERE `username`=:username' . $where;
        }

        $pdo = $adsky -> getPDO();
        $result = $pdo -> query('SELECT COUNT(*) FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '`');
        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

        $rows = $result -> fetchColumn();

        $itemsPerPage = $adsky -> getWebsiteSettings() -> getWebsitePaginatorItemsPerPage();
        $maxPage = ceil($rows / $itemsPerPage);
        if($page > $maxPage) {
            $page = $maxPage;
        }

        $min = ($page - 1) * $itemsPerPage;
        $max = $min + $itemsPerPage;

        if($min != 0) {
            $max = $max - 1;
        }

        $statement = $pdo -> prepare('SELECT * FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '`' . $where . ' LIMIT ' . $min . ', ' . $max);
        if($statement -> execute(['username' => $username])) {
            $data = [];
            foreach($statement -> fetchAll() as $row) {
                array_push($data, [
                    'username' => $row['username'],
                    'type' => intval($row['type']),
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'interval' => intval($row['interval']),
                    'expiration' => intval($row['until']),
                    'duration' => intval($row['duration'])
                ]);
            }

            return new Response(null, $adsky -> getLanguageString('API_SUCCESS'), [
                'data' => $data,
                'page' => $page,
                'maxPage' => $maxPage,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $maxPage
            ]);
        }

        return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public static function pay($renew, callable $callback) {
        $adsky = AdSky::getInstance();

        $hasSuccess = isset($_GET['success']) && strlen($_GET['success']) !== 0;

        if($hasSuccess) {
            if($_GET['success'] !== 'true') {
                header('Location: ../../admin/?' . ($renew ? 'message=renew_error#list' : 'message=create_error#create'));
                die();
            }

            $_POST = array_merge($_GET, $_POST);
        }

        require '../objects/User.php';

        $auth = $adsky -> getAuth();
        if(!isset($_GET['success'])) {
            try {
                $auth -> throttle([
                    'ad-pay',
                    $_SERVER['REMOTE_ADDR']
                ], 5, 60);
            }
            catch(Auth\TooManyRequestsException $error) {
                (new Response($adsky -> getLanguageString('API_ERROR_TOOMANYREQUESTS'), null, $error)) -> returnResponse();
            }
            catch(Auth\AuthError $error) {
                (new Response($adsky -> getLanguageString('API_ERROR_GENERIC_AUTH_ERROR'), null, $error)) -> returnResponse();
            }
        }

        $user = User::isLoggedIn() -> _object;

        if($user == null) {
            (new Response($adsky -> getLanguageString('API_ERROR_NOT_LOGGEDIN'))) -> returnResponse();
        }

        $ad = null;
        if($renew) {
            if(!isset($_POST['type']) || strlen($_POST['type']) === self::TYPE_TITLE || empty($_POST['title']) || empty($_POST['days'])) {
                (new Response($adsky -> getLanguage() -> formatNotSet([$adsky -> getLanguageString('API_ERROR_NOT_SET_TYPE'), $adsky -> getLanguageString('API_ERROR_NOT_SET_TITLE'), $adsky -> getLanguageString('API_ERROR_NOT_SET_DAYS')]))) -> returnResponse();
            }

            if($_POST['days'] <= 0) {
                (new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'))) -> returnResponse();
            }

            $statement = $adsky -> getPDO() -> prepare('SELECT `interval`, `until` FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `title`=:title');
            $result = $statement -> execute(['title' => $_POST['title']]);

            if(!$result) {
                (new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'))) -> returnResponse();
            }

            $row = $statement -> fetch();
            if($row == null) {
                (new Response($adsky -> getLanguageString('API_ERROR_NOT_FOUND'))) -> returnResponse();
            }

            $adSettings = $adsky -> getAdSettings();
            $max = ($_POST['type'] == self::TYPE_TITLE ? $adSettings -> getTitleAdMaximumExpiration() : $adSettings -> getChatAdMaximumExpiration()) - (($row['expiration'] - mktime(0, 0, 0)) / (60 * 60 * 24));
            if($max <= ($_POST['type'] == self::TYPE_TITLE ? $adSettings -> getTitleAdMinimumExpiration() : $adSettings -> getChatAdMaximumExpiration()) || $_POST['days'] > $max) {
                (new Response($adsky -> getLanguageString('API_ERROR_INVALID_RENEWDAY'))) -> returnResponse();
            }

            $ad = new Ad($user['username'], intval($_POST['type']), $_POST['title'], null, $row['interval'], $row['until']);
        }
        else {
            if(!isset($_POST['type']) || strlen($_POST['type']) === 0 || empty($_POST['title']) || empty($_POST['message']) || empty($_POST['interval']) || empty($_POST['expiration'])) {
                (new Response($adsky -> getLanguageString('API_ERROR_NOT_SET_TOOMANY'))) -> returnResponse();
            }

            $validateResult = Ad::validate($_POST['type'], $_POST['title'], $_POST['message'], $_POST['interval'], $_POST['expiration'], Utils::notEmptyOrNull($_POST, 'duration'));
            if($validateResult -> _error != null) {
                $validateResult -> returnResponse();
            }

            $ad = new Ad($user['username'], intval($_POST['type']), $_POST['title'], $_POST['message'], intval($_POST['interval']), intval($_POST['expiration']), Utils::notEmptyOrNull($_POST, 'duration'));
        }

        if($user['type'] == 0) {
            call_user_func_array($callback, [$ad]);
        }

        $transaction = $ad -> createPayPalTransaction($renew ? intval($_POST['days']) : null);
        $apiContext = $adsky -> getPayPalSettings() -> getPayPalAPIContext();

        if($hasSuccess) {
            try {
                $payment = Payment::get($_POST['paymentId'], $apiContext);

                $execution = new PaymentExecution();
                $execution -> setPayerId($_POST['PayerID']);

                $payment -> execute($execution, $apiContext);
                call_user_func_array($callback, [$ad, 'Location: ']);
            }
            catch(Exception $error) {
                (new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_PAY'))) -> returnResponse();
            }
        }

        $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');

        $redirectUrls = new RedirectUrls();
        $redirectUrls
            -> setReturnUrl($link . '?success=true&' . http_build_query($_POST))
            -> setCancelUrl($link . '?success=false');

        $payer = new PayPal\Api\Payer();
        $payer -> setPaymentMethod('paypal');

        $payment = new Payment();
        $payment
            -> setIntent('sale')
            -> setPayer($payer)
            -> setTransactions(array($transaction))
            -> setRedirectUrls($redirectUrls);

        try {
            $payment -> create($apiContext);
            (new Response(null, $adsky -> getLanguageString('API_SUCCESS'), $payment -> getApprovalLink())) -> returnResponse();
        }
        catch(Exception $ex) {
            (new Response($adsky -> getLanguageString('API_ERROR_PAYPAL_REQUEST'))) -> returnResponse();
        }
    }

    public static function deleteAdsFromUser($username) {
        $adsky = AdSky::getInstance();

        $statement = $adsky -> getPDO() -> prepare('DELETE FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `username`=:username');
        $result = $statement -> execute(['username' => $username]);

        return $result ? new Response(null, $adsky -> getLanguageString('API_SUCCESS')) : new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
    }

    public static function todayAds() {
        $adsky = AdSky::getInstance();

        $result = $adsky -> getPDO() -> query('SELECT * FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` > UNIX_TIMESTAMP(CONVERT_TZ(DATE(SUBDATE(NOW(), 1)), \'+00:00\', \'SYSTEM\'))');
        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

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

    public static function deleteExpired() {
        $adsky = AdSky::getInstance();

        $result = $adsky -> getPDO() -> query('SELECT * FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))');
        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

        $result -> execute();
        if(!$result) {
            return new Response($adsky -> getLanguageString('API_ERROR_MYSQL_ERROR'));
        }

        return new Response(null, $adsky -> getLanguageString('API_SUCCESS'));
    }

    public static function getNumberOfAdsPerDay() {
        $adsky = AdSky::getInstance();
        $sum = $adsky -> getPDO() -> query('SELECT SUM(`interval`) FROM `' . ($adsky -> getMySQLSettings() -> getAdsTable()) . '`');
        return $sum -> fetchColumn();
    }

}