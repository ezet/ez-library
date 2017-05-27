<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * Performs basic session handling
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Session {

    /**
     * Session lifetime, retrieved from Config
     * @var <type>
     */
    private $_lifetime;

    /**
     * Basic factory method
     * @return self
     */
    public static function factory() {
        return new self;
    }

    /**
     * Constructor
     * Sets the lifetime as per Config, and starts a session
     */
    public function __construct() {
        $this->_lifetime = Config::get('session_lifetime');
        $this->open();
    }

    /**
     * Destructor
     * Closes the session explicitly
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Magic getter for the $_SESSION superglobal
     * @param <type> $key
     * @return <type>
     */
    public function __get($key) {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

    /**
     * Magic setter for the $_SESSION superglobal
     * @param <type> $key
     * @param <type> $value
     */
    public function __set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Gets a value from a nested array in $_SESSION
     * @param <type> $key
     * @param <type> $subkey
     * @return <type>
     */
    public function getArrayValue($key, $subkey) {
        return (isset($_SESSION[$key][$subkey])) ? $_SESSION[$key][$subkey] : null;
    }

    /**
     * Sets a value to a nested array in $_SESSION
     * @param <type> $key
     * @param <type> $subkey
     * @param <type> $value
     */
    public function setArrayValue($key, $subkey, $value) {
        $_SESSION[$key][$subkey] = $value;
    }

    /**
     * Returns all keys in $_SESSION
     * @return <type>
     */
    public function getKeys() {
        return array_keys($_SESSION);
    }

    /**
     * Returns the current session save path
     * @return <type>
     */
    public function getSavePath() {
        return session_save_path();
    }

    /**
     * returns the session name
     * @return <type>
     */
    public function getSessionName() {
        return session_name();
    }

    /**
     * Returns the session id
     * @return <type>
     */
    public function getSessionId() {
        return session_id();
    }

    /**
     * Sets the session id
     * @param <type> $value
     */
    public function setSessionId($value) {
        session_id($value);
    }

    /**
     * Returns the whole session array
     * @return <type>
     */
    public function toArray() {
        return $_SESSION;
    }

    /**
     * Removes and returns a value from session, can used for flash storage
     * @param <type> $key
     * @return <type>
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        } else
            return null;
    }

    /**
     * Starts a new session
     */
    public function open() {
        session_set_cookie_params($this->_lifetime);
        @session_start();
    }

    /*
     * Clears the session
     */
    public function clear() {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Closes the session
     */
    public function close() {
        @session_write_close();
    }

    /**
     * Destroys the session
     */
    public function destroy() {
        @session_unset();
        @session_destroy();
    }

}