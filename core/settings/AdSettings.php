<?php

namespace AdSky\Core\Settings;

use AdSky\Core\Objects\Ad;

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
    
    public function validateTitle($title, $type) {
        if($type == Ad::TYPE_TITLE) {
            if(!($this -> getTitleAdTitleMinimumCharactersCount() <= strlen($title) && strlen($title) <= $this -> getTitleAdTitleMaximumCharactersCount())) {
                return false;
            }
        }
        else {
            if(!($this -> getChatAdTitleMinimumCharactersCount() <= strlen($title) && strlen($title) <= $this -> getChatAdTitleMaximumCharactersCount())) {
                return false;
            }
        }
        
        return true;
    }
    
    public function validateMessage($message, $type) {
        if($type == Ad::TYPE_TITLE) {
            if(!($this -> getTitleAdMessageMinimumCharactersCount() <= strlen($message) && strlen($message) <= $this -> getTitleAdMessageMaximumCharactersCount())) {
                return false;
            }
        }
        else {
            if(!($this -> getChatAdMessageMinimumCharactersCount() <= strlen($message) && strlen($message) <= $this -> getChatAdMessageMaximumCharactersCount())) {
                return false;
            }
        }
        
        return true;
    }
    
    public function validateInterval($interval, $type) {
        if($type == Ad::TYPE_TITLE) {
            if(!($this -> getTitleAdMinimumDisplayPerDay() <= $interval && $interval <= $this -> getTitleAdMaximumDisplayPerDay())) {
                return false;
            }
        }
        else {
            if(!($this -> getChatAdMinimumDisplayPerDay() <= $interval && $interval <= $this -> getChatAdMaximumDisplayPerDay())) {
                return false;
            }
        }
        
        return true;
    }
    
    public function validateExpiration($expiration, $type) {
        $today = gmmktime(0, 0, 0);

        if($type == Ad::TYPE_TITLE) {
            if(!($today + ($this -> getTitleAdMinimumExpiration() * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($this -> getTitleAdMaximumExpiration() * 60 * 60 * 24))) {
                return false;
            }
        }
        else {
            if(!($today + ($this -> getChatAdMinimumExpiration() * 60 * 60 * 24) <= $expiration && $expiration <= $today + ($this -> getChatAdMaximumExpiration() * 60 * 60 * 24))) {
                return false;
            }
        }
        
        return true;
    }
    
    public function validateDuration($duration) {
        return $this -> getTitleAdMinimumSecondsToDisplay() <= $duration && $duration <= $this -> getTitleAdMaximumSecondsToDisplay();
    }

    public function validate($title, $message, $interval, $expiration, $duration, $type) {
        return
            $this -> validateTitle($title, $type) &&
            $this -> validateMessage($message, $type) &&
            $this -> validateInterval($interval, $type) &&
            $this -> validateExpiration($expiration, $type) &&
            ($type == Ad::TYPE_TITLE ? $this -> validateDuration($duration) : true);
    }

}