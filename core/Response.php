<?php

namespace AdSky\Core;

/**
 * Represents an API response.
 */

class Response {

    public $error;
    public $message;
    public $object;

    /**
     * Creates a new response instance.
     *
     * @param string $error If there is an error, specify it here.
     * @param string $message If there is a message, specify it here.
     * @param mixed $object If there is an object, specify it here.
     */

    public function __construct($error = null, $message = null, $object = null) {
        $this -> error = $error;
        $this -> message = $message;
        $this -> object = $object;
    }

    /**
     * Gets the error.
     *
     * @return null|string The error.
     */

    public function getError() {
        return $this -> error;
    }

    /**
     * Sets the error.
     *
     * @param string $error The error.
     */

    public function setError($error) {
        $this -> error = $error;
    }

    /**
     * Gets the message.
     *
     * @return null|string The message.
     */

    public function getMessage() {
        return $this -> message;
    }

    /**
     * Sets the message.
     *
     * @param string $message The message.
     */

    public function setMessage($message) {
        $this -> message = $message;
    }

    /**
     * Gets the object.
     *
     * @return null|string The object.
     */

    public function getObject() {
        return $this -> object;
    }

    /**
     * Sets the object.
     *
     * @param mixed $object The object.
     */

    public function setObject($object) {
        $this -> object = $object;
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

    /**
     * Creates a new response instance and call returnResponse();
     *
     * @param string $error If there is an error, specify it here (must be a Language key).
     * @param string $message If there is a message, specify it here (must be a Language key).
     * @param mixed $object If there is an object, specify it here.
     */

    public static function createAndReturn($error = null, $message = null, $object = null) {
        $language = AdSky::getInstance() -> getLanguage();

        if($error != null) {
            if(is_array($error)) {
                $errorContent = [];
                foreach($error as $errorKey) {
                    array_push($errorContent, $language -> getSettings($errorKey));
                }

                $error = $language -> formatNotSet($errorContent);
            }
            else {
                $error = $language -> getSettings($error);
            }
        }

        $response = new Response($error, $message == null ? null : $language -> getSettings($message), $object);
        $response -> returnResponse();
    }

    public function __toString() {
        return json_encode([
            'error' => $this -> error,
            'message' => $this -> message,
            'object' => !AdSky::APP_DEBUG && $this -> error != null ? null : $this -> object
        ]);
    }

}