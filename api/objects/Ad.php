<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Lang.php';

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../Settings.php';

use Delight\Auth;
use PayPal\Api\Amount;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

global $settings;
define('ADS_TABLE', $settings['DB_PREFIX'] . 'ads');

class Ad {

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

    public function register($pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        global $lang;

        if(self::adExists($this -> _title, $pdo)) {
            return new Response($lang['API_ERROR_SAME_NAME']);
        }

        $statement = $pdo -> prepare('INSERT INTO `' . ADS_TABLE . '` VALUES (NULL, :title, :message, :username, :interval, :until, :type, :duration)');
        if($statement -> execute([
            'title' => htmlspecialchars($this -> _title),
            'message' => htmlspecialchars($this -> _message),
            'username' => htmlspecialchars($this -> _username),
            'interval' => $this -> _interval,
            'until' => $this -> _expiration,
            'type' => $this -> _type,
            'duration' => $this -> _duration
        ])) {
            return new Response(null,  $lang['API_SUCCESS']);
        }

        return new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public function update($type = null, $title = null, $message = null, $interval = null, $expiration = null, $duration = null, $pdo = null) {
        global $lang;

        if($pdo == null) {
            $pdo = getPDO();
        }

        try {
            $auth = createAuth($pdo);
            $auth -> throttle([
                'ad-update',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }

        if($title != null) {
            $title = htmlspecialchars($title);
        }

        if($title != ($this -> _title) && self::adExists($title, $pdo)) {
            return new Response($lang['API_ERROR_SAME_NAME']);
        }

        $where = ' WHERE `type`=' . $this -> _type . ' AND `title`=\'' . $this -> _title . '\' AND `username`=\'' . $this -> _username . '\'';

        if($type != null) {
            if(!ctype_digit($type) || ($type != 0 && $type != 1)) {
                $type = 0;
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

        if($duration != null) {
            if((!ctype_digit($duration) || $duration < 1) && $type == 0) {
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

            $statement = $pdo -> prepare('UPDATE `' . ADS_TABLE . '` SET' . substr($values, 1) . $where);
            $result = $statement -> execute($data);

            if($statement -> rowCount() === 0) {
                return new Response($lang['API_ERROR_NOT_FOUND']);
            }
        }

        return $result ? new Response(null, $lang['API_SUCCESS']) : new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public function delete($pdo = null) {
        global $lang;

        if($pdo == null) {
            $pdo = getPDO();
        }

        try {
            $auth = createAuth($pdo);
            $auth -> throttle([
                'ad-delete',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }

        $statement = $pdo -> prepare('DELETE FROM `' . ADS_TABLE . '` WHERE `type`=:type AND `title`=:title AND `username`=:username');
        $result = $statement -> execute([
            'type' => $this -> _type,
            'title' => $this -> _title,
            'username' => $this -> _username
        ]);

        if($statement -> rowCount() === 0) {
            return new Response($lang['API_ERROR_NOT_FOUND']);
        }

        return $result ? new Response(null, $lang['API_SUCCESS']) : new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public function renew($days, $pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        $statement = $pdo -> prepare('UPDATE `' . ADS_TABLE . '` SET `until` = `until` + :days WHERE `type`=:type AND `title`=:title');
        $result = $statement -> execute([
            'days' => 60 * 60 * 24 * $days,
            'type' => $this -> _type,
            'title' => $this -> _title
        ]);

        global $lang;

        if($statement -> rowCount() === 0) {
            return new Response($lang['API_ERROR_NOT_FOUND']);
        }

        return $result ? new Response(null, $lang['API_SUCCESS']) : new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public function createPayPalTransaction($totalDays = null) {
        global $settings;
        global $lang;

        if($totalDays == null) {
            $totalDays = (intval($this -> _expiration) - mktime(0, 0, 0)) / (60 * 60 * 24);
        }

        $amount = new Amount();
        $amount -> setTotal(($this -> _type == 0 ? $settings['AD_TITLE_COST'] : $settings['AD_CHAT_COST']) * intval($this -> _interval) * $totalDays);
        $amount -> setCurrency($settings['APP_CURRENCY']);

        $transaction = new Transaction();
        $transaction
            -> setAmount($amount)
            -> setDescription(sprintf($lang['API_PAYPAL_ITEM'], $this -> _interval, ($this -> _type == 0 ? $lang['AD_TYPE_TITLE'] : $lang['AD_TYPE_CHAT']), $totalDays));

        return $transaction;
    }

    public static function validate($type, $title, $message, $interval, $expiration, $duration, $pdo = null) {
        $type = intval($type);
        $duration = intval($duration);
        $interval = intval($interval);

        $expirationDate = new DateTime();
        $expirationDate -> setTimestamp(intval($expiration));
        $expirationDate -> setTime(0, 0, 0);

        $expiration = $expirationDate -> getTimestamp();

        $titleLength = strlen($title);
        $messageLength = strlen($message);

        global $settings;
        global $lang;

        if($type != 0 && $type != 1) {
            return new Response($lang['API_ERROR_INVALID_TYPE']);
        }

        if(Ad::adExists($title, $pdo)) {
            return new Response($lang['API_ERROR_SAME_NAME']);
        }

        if($type == 0) {
            if(!($settings['AD_TITLE_LIMIT_TITLE_CHARS_MIN'] <= $titleLength && $titleLength <= $settings['AD_TITLE_LIMIT_TITLE_CHARS_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_TITLE_LENGTH']);
            }

            if(!($settings['AD_TITLE_LIMIT_MESSAGE_CHARS_MIN'] <= $messageLength && $messageLength <= $settings['AD_TITLE_LIMIT_MESSAGE_CHARS_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_MESSAGE_LENGTH']);
            }

            if($duration == null || !($settings['AD_TITLE_LIMIT_SECONDS_MIN'] <= $duration && $duration <= $settings['AD_TITLE_LIMIT_SECONDS_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_DURATION']);
            }

            if(!($settings['AD_TITLE_LIMIT_DAY_MIN'] <= $interval && $interval <= $settings['AD_TITLE_LIMIT_DAY_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_INTERVAL']);
            }

            $today = mktime(0, 0, 0);

            if(!($today + ($settings['AD_TITLE_LIMIT_EXPIRATION_MIN'] * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($settings['AD_TITLE_LIMIT_EXPIRATION_MAX'] * 60 * 60 * 24))) {
                return new Response($lang['API_ERROR_INVALID_EXPIRATIONDATE']);
            }
        }
        else {
            if(!($settings['AD_CHAT_LIMIT_TITLE_CHARS_MIN'] <= $titleLength && $titleLength <= $settings['AD_CHAT_LIMIT_TITLE_CHARS_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_TITLE_LENGTH']);
            }

            if(!($settings['AD_CHAT_LIMIT_MESSAGE_CHARS_MIN'] <= $messageLength && $messageLength <= $settings['AD_CHAT_LIMIT_MESSAGE_CHARS_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_MESSAGE_LENGTH']);
            }

            if(!($settings['AD_CHAT_LIMIT_DAY_MIN'] <= $interval && $interval <= $settings['AD_CHAT_LIMIT_DAY_MAX'])) {
                return new Response($lang['API_ERROR_INVALID_INTERVAL']);
            }

            $today = mktime(0, 0, 0);
            if(!($today + ($settings['AD_CHAT_LIMIT_EXPIRATION_MIN'] * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($settings['AD_CHAT_LIMIT_EXPIRATION_MAX'] * 60 * 60 * 24))) {
                return new Response($lang['API_ERROR_INVALID_EXPIRATIONDATE']);
            }
        }

        if(Ad::getNumberOfAdsPerDay() + $interval > $settings['AD_PER_DAY_LIMIT']) {
            return new Response($lang['API_ERROR_LIMIT_REACHED']);
        }

        return new Response(null, $lang['API_SUCCESS']);
    }

    public static function adExists($title, $pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        $statement = $pdo -> prepare('SELECT `title` FROM `' . ADS_TABLE . '` WHERE `title`=:title LIMIT 1');
        $statement -> execute(['title' => $title]);
        return $statement -> fetch() != false;
    }

    public static function getAds($page = null, $username = null, $pdo = null) {
        global $settings;
        global $lang;

        if($pdo == null) {
            $pdo = getPDO();
        }

        try {
            $auth = createAuth($pdo);
            $auth -> throttle([
                'ad-get',
                $_SERVER['REMOTE_ADDR']
            ], 10, 60);
        }
        catch(Auth\TooManyRequestsException $error) {
            return new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error);
        }
        catch(Auth\AuthError $error) {
            return new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error);
        }

        if($page == null || $page < 1) {
            $page = 1;
        }
        $page = intval($page);

        $where = ' ORDER BY `title`';
        if($username != null) {
            $where = ' WHERE `username`=:username' . $where;
        }

        $result = $pdo -> query('SELECT COUNT(*) FROM `' . ADS_TABLE . '`');
        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
        }

        $rows = $result -> fetchColumn();

        $maxPage = ceil($rows / $settings['PAGINATOR_MAX']);
        if($page > $maxPage) {
            $page = $maxPage;
        }

        $min = ($page - 1) * $settings['PAGINATOR_MAX'];
        $max = $min + $settings['PAGINATOR_MAX'];

        if($min != 0) {
            $max = $max - 1;
        }

        $statement = $pdo -> prepare('SELECT * FROM `' . ADS_TABLE . '`' . $where . ' LIMIT ' . $min . ', ' . $max);
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

            return new Response(null, $lang['API_SUCCESS'], [
                'data' => $data,
                'page' => $page,
                'maxPage' => $maxPage,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $maxPage
            ]);
        }

        return new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public static function pay($renew, callable $callback) {
        global $settings;
        global $lang;

        $hasSuccess = isset($_GET['success']) && strlen($_GET['success']) !== 0;

        if($hasSuccess) {
            if($_GET['success'] !== 'true') {
                header('Location: ../../admin.php?' . ($renew ? 'message=renew_error#list' : 'message=create_error#create'));
                die();
            }

            $_POST = array_merge($_GET, $_POST);
        }

        require '../objects/User.php';

        $pdo = getPDO();
        $auth = createAuth($pdo);

        if(!isset($_GET['success'])) {
            try {
                $auth -> throttle([
                    'ad-pay',
                    $_SERVER['REMOTE_ADDR']
                ], 5, 60);
            }
            catch(Auth\TooManyRequestsException $error) {
                (new Response($lang['API_ERROR_TOOMANYREQUESTS'], null, $error)) -> returnResponse();
            }
            catch(Auth\AuthError $error) {
                (new Response($lang['API_ERROR_GENERIC_AUTH_ERROR'], null, $error)) -> returnResponse();
            }
        }

        $user = User::isLoggedIn($auth) -> _object;

        if($user == null) {
            (new Response($lang['API_ERROR_NOT_LOGGEDIN'])) -> returnResponse();
        }

        $ad = null;
        if($renew) {
            if(!isset($_POST['type']) || strlen($_POST['type']) === 0 || empty($_POST['title']) || empty($_POST['days'])) {
                (new Response(formatNotSet([$lang['API_ERROR_NOT_SET_TYPE'], $lang['API_ERROR_NOT_SET_TITLE'], $lang['API_ERROR_NOT_SET_DAYS']]))) -> returnResponse();
            }

            if($_POST['days'] <= 0) {
                (new Response($lang['API_ERROR_INVALID_RENEWDAY'])) -> returnResponse();
            }

            $statement = $pdo -> prepare('SELECT `interval`, `until` FROM `' . ADS_TABLE . '` WHERE `title`=:title');
            $result = $statement -> execute(['title' => $_POST['title']]);

            if(!$result) {
                (new Response($lang['API_ERROR_MYSQL_ERROR'])) -> returnResponse();
            }

            $row = $statement -> fetch();
            if($row == null) {
                (new Response($lang['API_ERROR_NOT_FOUND'])) -> returnResponse();
            }

            $max = ($_POST['type'] == 0 ? $settings['AD_TITLE_LIMIT_EXPIRATION_MAX'] : $settings['AD_CHAT_LIMIT_EXPIRATION_MAX']) - (($row['expiration'] - mktime(0, 0, 0)) / (60 * 60 * 24));
            if($max <= ($_POST['type'] == 0 ? $settings['AD_TITLE_LIMIT_EXPIRATION_MIN'] : $settings['AD_CHAT_LIMIT_EXPIRATION_MIN']) || $_POST['days'] > $max) {
                (new Response($lang['API_ERROR_INVALID_RENEWDAY'])) -> returnResponse();
            }

            $ad = new Ad($user['username'], intval($_POST['type']), $_POST['title'], null, $row['interval'], $row['until']);
        }
        else {
            if(!isset($_POST['type']) || strlen($_POST['type']) === 0 || empty($_POST['title']) || empty($_POST['message']) || empty($_POST['interval']) || empty($_POST['expiration'])) {
                (new Response($lang['API_ERROR_NOT_SET_TOOMANY'])) -> returnResponse();
            }

            $validateResult = Ad::validate($_POST['type'], $_POST['title'], $_POST['message'], $_POST['interval'], $_POST['expiration'], utilNotEmptyOrNull($_POST, 'duration'), $pdo);
            if($validateResult -> _error != null) {
                $validateResult -> returnResponse();
            }

            $ad = new Ad($user['username'], intval($_POST['type']), $_POST['title'], $_POST['message'], intval($_POST['interval']), intval($_POST['expiration']), utilNotEmptyOrNull($_POST, 'duration'));
        }

        if($user['type'] == 0) {
            call_user_func_array($callback, [$ad, $pdo]);
        }

        $transaction = $ad -> createPayPalTransaction($renew ? intval($_POST['days']) : null);
        $apiContext = getPayPalAPI();

        if($hasSuccess) {
            try {
                $payment = Payment::get($_POST['paymentId'], $apiContext);

                $execution = new PaymentExecution();
                $execution -> setPayerId($_POST['PayerID']);

                $payment -> execute($execution, $apiContext);
                call_user_func_array($callback, [$ad, $pdo, 'Location: ']);
            }
            catch(Exception $error) {
                (new Response($lang['API_ERROR_PAYPAL_PAY'])) -> returnResponse();
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
            (new Response(null, $lang['API_SUCCESS'], $payment -> getApprovalLink())) -> returnResponse();
        }
        catch(Exception $ex) {
            (new Response($lang['API_ERROR_PAYPAL_REQUEST'])) -> returnResponse();
        }
    }

    public static function deleteAdsFromUser($username, $pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        $statement = $pdo -> prepare('DELETE FROM `' . ADS_TABLE . '` WHERE `username`=:username');
        $result = $statement -> execute(['username' => $username]);

        global $lang;
        return $result ? new Response(null, $lang['API_SUCCESS']) : new Response($lang['API_ERROR_MYSQL_ERROR']);
    }

    public static function todayAds($pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        global $lang;

        $result = $pdo -> query('SELECT * FROM `' . ADS_TABLE . '` WHERE `until` > UNIX_TIMESTAMP(CONVERT_TZ(DATE(SUBDATE(NOW(), 1)), \'+00:00\', \'SYSTEM\'))');
        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
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

        return new Response(null, $lang['API_SUCCESS'], $object);
    }

    public static function deleteExpired($pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        global $lang;

        $result = $pdo -> query('SELECT * FROM `' . ADS_TABLE . '` WHERE `until` <= UNIX_TIMESTAMP(CONVERT_TZ(DATE(NOW()), \'+00:00\', \'SYSTEM\'))');
        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
        }

        $result -> execute();
        if(!$result) {
            return new Response($lang['API_ERROR_MYSQL_ERROR']);
        }

        return new Response(null, $lang['API_SUCCESS']);
    }

    public static function getNumberOfAdsPerDay($pdo = null) {
        if($pdo == null) {
            $pdo = getPDO();
        }

        $sum = $pdo -> query('SELECT SUM(`interval`) FROM `' . ADS_TABLE . '`');
        return $sum -> fetchColumn();
    }

}