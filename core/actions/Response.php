<?php

namespace AdSky\Core\Actions;

use AdSky\Core\AdSky;
use AdSky\Core\Lang\Language;

/**
 * Represents an API response.
 */

class Response {

    private $language;

    private $error;
    private $message;
    private $object;

    /**
     * Creates a new response instance.
     *
     * @param string $error If there is an error, specify it here.
     * @param string $message If there is a message, specify it here.
     * @param mixed $object If there is an object, specify it here.
     * @param Language language If you have a language instance, specify it here.
     */

    public function __construct($error = null, $message = null, $object = null, $language = null) {
        $this -> language = $language == null ? AdSky::getInstance() -> getLanguage() : $language;

        $this -> setError($error);
        $this -> setMessage($message);
        $this -> setObject($object);
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
     * @param string $error The error (language key or not).
     */

    public function setError($error) {
        $this -> error = $error != null && $this -> language -> has($error) ? $this -> language -> getSettings($error) : $error;
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
     * @param string $message The message (language key or not).
     */

    public function setMessage($message) {
        $this -> message = $message != null && $this -> language -> has($message) ? $this -> language -> getSettings($message) : $message;
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
     * Creates a new "xxxx not set" error.
     *
     * @param array $keys The not set keys.
     * @param Language $language The language.
     *
     * @return Response The error Response.
     */

    public static function notSet($keys, $language = null) {
        if($language == null) {
            $language = AdSky::getInstance() -> getLanguage();
        }

        $content = [];
        foreach($keys as $key) {
            array_push($content, $language -> getSettings($key));
        }

        return new Response($language -> formatNotSet($content), null, null, $language);
    }

    public function __toString() {
        return json_encode([
            'error' => $this -> error,
            'message' => $this -> message,
            'object' => !AdSky::APP_DEBUG && $this -> error != null ? null : $this -> object
        ]);
    }

}