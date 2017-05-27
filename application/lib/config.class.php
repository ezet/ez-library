<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * VERY simple configuration class
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Config {

    private static $_config = array(
        'dbinfo' => array(
//          DB CONFIG
            'type' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 'ez_library',
            'username' => 'root',
            'password' => ''
        ),
//       MISC CONFIG
        'default_pagetitle' => 'MyPage',
        'loginpage' => '',
        'default_layout' => 'default',
        'default_controller' => 'index',
        'default_action' => 'index',
        'session_lifetime' => 0,
    );

    // disallows instantiation
    private function __construct() {

    }

    // Returns a configuration value
    public static function get($key) {
        return (isset(self::$_config[$key])) ? self::$_config[$key] : null;
    }

    // Sets a configuration value
    public static function set($key, $value) {
        self::$_config[$key] = $value;
    }

}