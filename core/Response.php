<?php

class Response {

    public $_error;
    public $_message;
    public $_object;

    public function __construct($error = null, $message = null, $object = null) {
        $this -> _error = $error;
        $this -> _message = $message;
        $this -> _object = $object;
    }

    public function getError() {
        return $this -> _error;
    }

    public function setError($error) {
        $this -> _error = $error;
    }

    public function getMessage() {
        return $this -> _message;
    }

    public function setMessage($message) {
        $this -> _message = $message;
    }

    public function getObject() {
        return $this -> _object;
    }

    public function setObject($object) {
        $this -> _object = $object;
    }

    public function __toString() {
        return json_encode([
            'error' => $this -> _error,
            'message' => $this -> _message,
            'object' => !AdSky::APP_DEBUG && $this -> _error != null ? null : $this -> _object
        ]);
    }

    public function returnResponse() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Methods: POST');

        die($this);
    }

}