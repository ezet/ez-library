<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

use ezmvc\dao\UserDAO;
use ezmvc\lib\Session;

/**
 * Manages user authentication and other user services
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Auth {

    /**
     * Holds the usermodel if a user is logged in
     * @var <type>
     */
    private $_user;
    /**
     * Key for aiding in password hashing
     * @var <type>
     */
    private static $_key = 'bordsalt';
    /**
     * Length of generated salt
     * @var <type>
     */
    private static $_saltlen = 10;
    /**
     * Session object for handling the session
     * @var <type>
     */
    private $_session;

    /**
     * Basic factory method, injects a Session object
     * @return self
     */
    public static function factory() {
        return new self(Session::factory());
    }

    /**
     * Constructor
     * Stores the session object
     * @param Session $session
     */
    public function __construct(Session $session) {
        $this->_session = $session;
    }

    /**
     * Authenticates the given username and password against the database,
     * Logs the user in and returns a user object on success, otherwise returns false
     * @param <type> $username
     * @param <type> $password
     * @return <type>
     */
    public function authenticate($username, $password) {
        // Find the user in the db
        $dao = UserDao::get();
        $user = $dao->findByUsername($username);

        // Perform validation
        if ($user == NULL || self::getHash($password, $user->Password) !== $user->Password) {
            return false;
        } else {
            // update information, such as login count and last login
            $dao->updateLoginInfo($user->UserId);
            $this->_user = $user;
            // complete the login
            $this->_login();
            return $user;
        }
    }

    /**
     * Performs a login by setting the session variables
     */
    private function _login() {
        $user = $this->_user;
        $this->_session->open();
        $this->_session->id = $user->UserId;
    }

    /**
     * Performs a login, just like Auth::login(), but is callable by the client,
     * bypassing authentication (used after registation eg.)
     * @param <type> $user
     */
    public function forceLogin($user) {
        $this->_session->open();
        $this->_session->id = $user->UserId;
    }

    /**
     * Logs a user out
     */
    public function logout() {
//        $this->_session->destroy();
        $this->_session->remove('id');
        $this->_session->remove('admin');
    }

    /**
     * Creates a string digest, using a defined key and a runtime-generated salt.
     * The salt is then stored together with the password, so it can be retrieved and used for comparisons
     * The hash is then created by using sha256.
     * By using a separate key and a salt, an attacker would need to compromise both the DB aswell as getting
     * the key, length of the salt, and how the salt is stored.
     * @param <type> $string
     * @param <type> $salt
     * @return <type>
     */
    public static function getHash($string, $salt=null) {
        $key = self::$_key;
        if ($salt) {
            $salt = substr($salt, 0, self::$_saltlen);
        } else {
            $salt = substr(hash('sha256', uniqid(rand(), true) . $key . microtime()), 0, self::$_saltlen);
        }
        return $salt . hash('sha256', $string . $salt . $key);
    }

    /**
     * Returns the userid if logged in
     * @return <type>
     */
    public function getId() {
        return $this->_session->id;
    }

    /**
     * Checks wether a user is logged in
     * @return <type>
     */
    public function isLogged() {
        return $this->getId();
    }

}