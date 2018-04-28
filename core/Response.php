<?php

/**
 * Represents an API response.
 */

class Response {

    public $_error;
    public $_message;
    public $_object;

    /**
     * Creates a new response instance.
     *
     * @param string $error If there is an error, specify it here.
     * @param string $message If there is a message, specify it here.
     * @param mixed $object If there is an object, specify it here.
     */

    public function __construct($error = null, $message = null, $object = null) {
        $this -> _error = $error;
        $this -> _message = $message;
        $this -> _object = $object;
    }

    /**
     * Gets the error.
     *
     * @return null|string The error.
     */

    public function getError() {
        return $this -> _error;
    }

    /**
     * Sets the error.
     *
     * @param string $error The error.
     */

    public function setError($error) {
        $this -> _error = $error;
    }

    /**
     * Gets the message.
     *
     * @return null|string The message.
     */

    public function getMessage() {
        return $this -> _message;
    }

    /**
     * Sets the message.
     *
     * @param string $message The message.
     */

    public function setMessage($message) {
        $this -> _message = $message;
    }

    /**
     * Gets the object.
     *
     * @return null|string The object.
     */

    public function getObject() {
        return $this -> _object;
    }

    /**
     * Sets the object.
     *
     * @param mixed $object The object.
     */

    public function setObject($object) {
        $this -> _object = $object;
    }

    /**
     * Returns the response with corresponding headers and die.
     */

    public function returnResponse() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Methods: POST');

        die($this);
    }

    public function __toString() {
        return json_encode([
            'error' => $this -> _error,
            'message' => $this -> _message,
            'object' => !AdSky::APP_DEBUG && $this -> _error != null ? null : $this -> _object
        ]);
    }

}