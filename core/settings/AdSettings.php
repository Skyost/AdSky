<?php

class AdSettings extends Settings {

    public function __construct() {
        $this -> putSettings('AD_PER_DAY_LIMIT', 15);

        $this -> putSettings('AD_TITLE_COST', 1.0);
        $this -> putSettings('AD_TITLE_LIMIT_TITLE_CHARS_MIN', 2);
        $this -> putSettings('AD_TITLE_LIMIT_TITLE_CHARS_MAX', 20);
        $this -> putSettings('AD_TITLE_LIMIT_MESSAGE_CHARS_MIN', 0);
        $this -> putSettings('AD_TITLE_LIMIT_MESSAGE_CHARS_MAX', 30);
        $this -> putSettings('AD_TITLE_LIMIT_DAY_MIN', 1);
        $this -> putSettings('AD_TITLE_LIMIT_DAY_MAX', 4);
        $this -> putSettings('AD_TITLE_LIMIT_SECONDS_MIN', 4);
        $this -> putSettings('AD_TITLE_LIMIT_SECONDS_MAX', 6);
        $this -> putSettings('AD_TITLE_LIMIT_EXPIRATION_MIN', 1);
        $this -> putSettings('AD_TITLE_LIMIT_EXPIRATION_MAX', 31);

        $this -> putSettings('AD_CHAT_COST', 0.2);
        $this -> putSettings('AD_CHAT_LIMIT_TITLE_CHARS_MIN', 2);
        $this -> putSettings('AD_CHAT_LIMIT_TITLE_CHARS_MAX', 20);
        $this -> putSettings('AD_CHAT_LIMIT_MESSAGE_CHARS_MIN', 0);
        $this -> putSettings('AD_CHAT_LIMIT_MESSAGE_CHARS_MAX', 140);
        $this -> putSettings('AD_CHAT_LIMIT_DAY_MIN', 1);
        $this -> putSettings('AD_CHAT_LIMIT_DAY_MAX', 12);
        $this -> putSettings('AD_CHAT_LIMIT_EXPIRATION_MIN', 1);
        $this -> putSettings('AD_CHAT_LIMIT_EXPIRATION_MAX', 31);
    }

    public function getAdPerDayLimit() {
        return $this -> getSettings('AD_PER_DAY_LIMIT');
    }

    public function getTitleAdCost() {
        return $this -> getSettings('AD_TITLE_COST');
    }

    public function getTitleAdTitleMinimumCharactersCount() {
        return $this -> getSettings('AD_TITLE_LIMIT_TITLE_CHARS_MIN');
    }

    public function getTitleAdTitleMaximumCharactersCount() {
        return $this -> getSettings('AD_TITLE_LIMIT_TITLE_CHARS_MAX');
    }

    public function getTitleAdMessageMinimumCharactersCount() {
        return $this -> getSettings('AD_TITLE_LIMIT_MESSAGE_CHARS_MIN');
    }

    public function getTitleAdMessageMaximumCharactersCount() {
        return $this -> getSettings('AD_TITLE_LIMIT_MESSAGE_CHARS_MAX');
    }

    public function getTitleAdMinimumDisplayPerDay() {
        return $this -> getSettings('AD_TITLE_LIMIT_DAY_MIN');
    }

    public function getTitleAdMaximumDisplayPerDay() {
        return $this -> getSettings('AD_TITLE_LIMIT_DAY_MAX');
    }

    public function getTitleAdMinimumSecondsToDisplay() {
        return $this -> getSettings('AD_TITLE_LIMIT_SECONDS_MIN');
    }

    public function getTitleAdMaximumSecondsToDisplay() {
        return $this -> getSettings('AD_TITLE_LIMIT_SECONDS_MAX');
    }

    public function getTitleAdMinimumExpiration() {
        return $this -> getSettings('AD_TITLE_LIMIT_EXPIRATION_MIN');
    }

    public function getTitleAdMaximumExpiration() {
        return $this -> getSettings('AD_TITLE_LIMIT_EXPIRATION_MAX');
    }

    public function getChatAdCost() {
        return $this -> getSettings('AD_CHAT_COST');
    }

    public function getChatAdTitleMinimumCharactersCount() {
        return $this -> getSettings('AD_CHAT_LIMIT_TITLE_CHARS_MIN');
    }

    public function getChatAdTitleMaximumCharactersCount() {
        return $this -> getSettings('AD_CHAT_LIMIT_TITLE_CHARS_MAX');
    }

    public function getChatAdMessageMinimumCharactersCount() {
        return $this -> getSettings('AD_CHAT_LIMIT_MESSAGE_CHARS_MIN');
    }

    public function getChatAdMessageMaximumCharactersCount() {
        return $this -> getSettings('AD_CHAT_LIMIT_MESSAGE_CHARS_MAX');
    }

    public function getChatAdMinimumDisplayPerDay() {
        return $this -> getSettings('AD_CHAT_LIMIT_DAY_MIN');
    }

    public function getChatAdMaximumDisplayPerDay() {
        return $this -> getSettings('AD_CHAT_LIMIT_DAY_MAX');
    }

    public function getChatAdMinimumExpiration() {
        return $this -> getSettings('AD_CHAT_LIMIT_EXPIRATION_MIN');
    }

    public function getChatAdMaximumExpiration() {
        return $this -> getSettings('AD_CHAT_LIMIT_EXPIRATION_MAX');
    }

}