<?php

require __DIR__ . '/Language.php';

/**
 * English translation of AdSky.
 */

class EnglishLanguage extends Language {
    
    public function __construct() {
        $this -> putSettings('AD_TYPE_TITLE', 'Title');
        $this -> putSettings('AD_TYPE_CHAT', 'Chat');

        $this -> putSettings('EMAIL_TITLE_RESET', 'Password reset');
        $this -> putSettings('EMAIL_TITLE_RESET_CONFIRMATION', 'Password reset confirmation');
        $this -> putSettings('EMAIL_TITLE_UPDATE', 'Update your email');
        $this -> putSettings('EMAIL_TITLE_CONFIRM', 'Confirm your email');

        $this -> putSettings('API_SUCCESS', 'Success.');

        $this -> putSettings('API_ERROR_MYSQL_ERROR', 'MySQL error.');

        $this -> putSettings('API_ERROR_INVALID_EMAIL', 'Invalid email.');
        $this -> putSettings('API_ERROR_INVALID_PASSWORD', 'Invalid password.');
        $this -> putSettings('API_ERROR_INVALID_CURRENT_PASSWORD', 'Invalid current password.');
        $this -> putSettings('API_ERROR_INVALID_SELECTOR', 'Invalid selector.');
        $this -> putSettings('API_ERROR_NOT_VERIFIED', 'Email not verified.');
        $this -> putSettings('API_ERROR_TOOMANYREQUESTS', 'Too many requests.');
        $this -> putSettings('API_ERROR_ATTEMPT_CANCELLED', 'Attempt cancelled error.');
        $this -> putSettings('API_ERROR_GENERIC_AUTH_ERROR', 'Generic authentication error.');
        $this -> putSettings('API_ERROR_GENERIC_ERROR', 'Generic error.');
        $this -> putSettings('API_ERROR_USERNAME_ALREADYEXISTS', 'Username already exists.');
        $this -> putSettings('API_ERROR_EMAIL_ALREADYEXISTS', 'Email already exists.');
        $this -> putSettings('API_ERROR_UNKNOWN_ID', 'Unknown ID.');
        $this -> putSettings('API_ERROR_TOKEN_EXPIRED', 'Token has expired.');
        $this -> putSettings('API_ERROR_RESET_DISABLED', 'Password reset is disabled.');

        $this -> putSettings('API_ERROR_USER_NOT_FOUND', 'User not found.');
        $this -> putSettings('API_ERROR_AD_NOT_FOUND', 'Ad not found.');
        $this -> putSettings('API_ERROR_SAME_NAME', 'An ad with the same name already exists.');

        $this -> putSettings('API_ERROR_PAYPAL_PAY', 'Can\'t pay via PayPal.');
        $this -> putSettings('API_ERROR_PAYPAL_REQUEST', 'Can\'t create a PayPal request.');

        $this -> putSettings('API_ERROR_INVALID_TYPE', 'Invalid type set.');
        $this -> putSettings('API_ERROR_INVALID_TITLE', 'Invalid title.');
        $this -> putSettings('API_ERROR_INVALID_MESSAGE', 'Invalid message.');
        $this -> putSettings('API_ERROR_INVALID_DURATION', 'Invalid duration.');
        $this -> putSettings('API_ERROR_INVALID_INTERVAL', 'Invalid interval.');
        $this -> putSettings('API_ERROR_INVALID_EXPIRATIONDATE', 'Invalid expiration date.');
        $this -> putSettings('API_ERROR_INVALID_RENEWDAY', 'Invalid renew day number set.');
        $this -> putSettings('API_ERROR_LIMIT_REACHED', 'Ad per day limit reached. Please try with a lower ad interval if possible.');

        $this -> putSettings('API_ERROR_INVALID_PLUGIN_KEY', 'Incorrect plugin key.');

        $this -> putSettings('API_ERROR_NOT_LOGGEDIN', 'You are not logged in.');
        $this -> putSettings('API_ERROR_NOT_ADMIN', 'You must be an admin in order to do that.');
        $this -> putSettings('API_ERROR_NOT_SET', '%s not set.');
        $this -> putSettings('API_ERROR_NOT_SET_TOOMANY', 'Missing at least one parameter.');
        $this -> putSettings('API_ERROR_NOT_SET_USERNAME', 'username');
        $this -> putSettings('API_ERROR_NOT_SET_EMAIL', 'email');
        $this -> putSettings('API_ERROR_NOT_SET_PASSWORD', 'password');
        $this -> putSettings('API_ERROR_NOT_SET_OLDPASSWORD', 'old password');
        $this -> putSettings('API_ERROR_NOT_SET_ID', 'ad ID');
        $this -> putSettings('API_ERROR_NOT_SET_TITLE', 'title');
        $this -> putSettings('API_ERROR_NOT_SET_OLDTITLE', 'old title');
        $this -> putSettings('API_ERROR_NOT_SET_TYPE', 'type');
        $this -> putSettings('API_ERROR_NOT_SET_OLDTYPE', 'old type');
        $this -> putSettings('API_ERROR_NOT_SET_DAYS', 'days');

        $this -> putSettings('API_PAYPAL_ITEM', '%d %s ad(s) per day during %d day(s)');
    }

    public function getLanguage() {
        return 'English';
    }

    /**
     * Formats the not set string according to the specified array of Strings.
     *
     * @param array $what The array of Strings.
     *
     * @return string The formatted String.
     */

    public function formatNotSet($what) {
        $string = ucfirst($what[0]);

        for($i = 1; $i < count($what); $i++) {
            $string .= ' / ' . $what[$i];
        }

        return sprintf($this -> getSettings('API_ERROR_NOT_SET'), $string);
    }

}