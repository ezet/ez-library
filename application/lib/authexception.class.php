<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * Handles Authorization exceptions
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class AuthException extends \Exception {

    /**
     * Redirects to the loginpage, called on every AuthException
     * @param <type> $login
     */
    public function redirect($login) {
        $loginpage = __BASE_URL . '/' . $login;
        echo 'Authentication exception: ' . $this->getMessage();
        echo '<p>You are being redirected to the login page in 5 seconds... Or <a href=' . $loginpage . '>click here</a> to redirect immediately.</p>';
        header('refresh: 5; url=' . $loginpage);
        exit(1);
    }

}