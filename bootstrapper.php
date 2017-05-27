<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc;

session_start();

// set error levels
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// set the application path
define('__APP_PATH', __BASE_PATH . '/application');

// set the library path
define('__LIB_PATH', __APP_PATH . '/lib');

// set the public web root path
//define('__BASE_URL', 'http://' . $_SERVER['SERVER_NAME']);
define('__BASE_URL', 'http://localhost:8888/www/dev/ez-library');

//set_include_path(__APP_PATH. '/lib/');

include __LIB_PATH . '/config.class.php';

// disable default autoloader
spl_autoload_register(null, false);
spl_autoload_extensions('.php, .class.php');

// DEBUG autoloader vardump
function autoLoader($class) {
    $class = str_replace(__NAMESPACE__, '', $class);
    $class = str_replace('\\', '/', $class);
    $file = __APP_PATH . strtolower($class) . '.class.php';
    if (file_exists($file)) {
        include $file;
    } else {
        throw new \Exception('Could not locate class: ' . $class);
        return false;
    }
}

// register my autoloader
spl_autoload_register(__NAMESPACE__ . '\autoLoader');