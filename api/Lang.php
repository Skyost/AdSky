<?php

$lang = [];

$lang['AD_TYPE_TITLE'] = 'Title';
$lang['AD_TYPE_CHAT'] = 'Chat';

$lang['API_SUCCESS'] = 'Success.';

$lang['API_ERROR_MYSQL_ERROR'] = 'MySQL error.';

$lang['API_ERROR_INVALID_EMAIL'] = 'Invalid email.';
$lang['API_ERROR_INVALID_PASSWORD'] = 'Invalid password.';
$lang['API_ERROR_INVALID_CURRENT_PASSWORD'] = 'Invalid current password.';
$lang['API_ERROR_INVALID_SELECTOR'] = 'Invalid selector.';
$lang['API_ERROR_NOT_VERIFIED'] = 'Email not verified.';
$lang['API_ERROR_TOOMANYREQUESTS'] = 'Too many requests.';
$lang['API_ERROR_ATTEMPT_CANCELLED'] = 'Attempt cancelled error.';
$lang['API_ERROR_GENERIC_AUTH_ERROR'] = 'Generic authentication error.';
$lang['API_ERROR_GENERIC_ERROR'] = 'Generic error.';
$lang['API_ERROR_USERNAME_ALREADYEXISTS'] = 'Username already exists.';
$lang['API_ERROR_EMAIL_ALREADYEXISTS'] = 'Email already exists.';
$lang['API_ERROR_UNKNOWN_ID'] = 'Unknown ID.';
$lang['API_ERROR_TOKEN_EXPIRED'] = 'Token has expired.';
$lang['API_ERROR_RESET_DISABLED'] = 'Password reset is disabled.';

$lang['API_ERROR_NOT_FOUND'] = 'Ad not found.';
$lang['API_ERROR_SAME_NAME'] = 'An ad with the same name already exists.';

$lang['API_ERROR_PAYPAL_PAY'] = 'Can\'t pay via PayPal.';
$lang['API_ERROR_PAYPAL_REQUEST'] = 'Can\'t create a PayPal request.';

$lang['API_ERROR_INVALID_TYPE'] = 'Invalid type set.';
$lang['API_ERROR_INVALID_TITLE_LENGTH'] = 'Invalid title length.';
$lang['API_ERROR_INVALID_MESSAGE_LENGTH'] = 'Invalid message length.';
$lang['API_ERROR_INVALID_DURATION'] = 'Invalid duration.';
$lang['API_ERROR_INVALID_INTERVAL'] = 'Invalid interval.';
$lang['API_ERROR_INVALID_EXPIRATIONDATE'] = 'Invalid expiration date.';
$lang['API_ERROR_INVALID_RENEWDAY'] = 'Invalid renew day number set.';

$lang['API_ERROR_INVALID_PLUGIN_KEY'] = 'Incorrect plugin key.';

$lang['API_ERROR_NOT_LOGGEDIN'] = 'You are not logged in.';
$lang['API_ERROR_NOT_ADMIN'] = 'You must be an admin in order to do that.';
$lang['API_ERROR_NOT_SET'] = '%s not set.';
$lang['API_ERROR_NOT_SET_TOOMANY'] = 'Missing at least one parameter.';
$lang['API_ERROR_NOT_SET_USERNAME'] = 'username';
$lang['API_ERROR_NOT_SET_EMAIL'] = 'email';
$lang['API_ERROR_NOT_SET_PASSWORD'] = 'password';
$lang['API_ERROR_NOT_SET_OLDPASSWORD'] = 'old password';
$lang['API_ERROR_NOT_SET_TITLE'] = 'title';
$lang['API_ERROR_NOT_SET_OLDTITLE'] = 'old title';
$lang['API_ERROR_NOT_SET_TYPE'] = 'type';
$lang['API_ERROR_NOT_SET_OLDTYPE'] = 'old type';
$lang['API_ERROR_NOT_SET_DAYS'] = 'days';

$lang['API_PAYPAL_ITEM'] = '%d %s ad(s) per day during %d day(s)';

function formatNotSet($what) {
    global $lang;
    $string = ucfirst($what[0]);

    for($i = 1; $i < count($what); $i++) {
        $string .= ' / ' . $what[$i];
    }

    return sprintf($lang['API_ERROR_NOT_SET'], $string);
}