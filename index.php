<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @version    $Id$
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */

namespace ezmvc;

use ezmvc\lib\Request;
use ezmvc\controller\FrontController;
use ezmvc\lib\AuthException;
use ezmvc\lib\Config;

// define the site path
$site_path = realpath(dirname(__FILE__));
define('__BASE_PATH', $site_path);

// set up the environment
include __BASE_PATH . '/bootstrapper.php';

//var_dump($_POST);
//var_dump($_GET);

// instantiate FrontController and route the request
try {
    FrontController::factory()->run();

    // catch any exceptions
} catch (AuthException $e) {
    $e->redirect(Config::get('loginpage'));
} catch (\ErrorException $e) {
    trigger_error($e->getMessage(), E_USER_ERROR);
//    die('Fatal exception: ' . $e->getMessage());
//    var_dump($e);
} catch (\Exception $e) {
    trigger_error($e->getMessage(), E_USER_ERROR);
//    die('Exception caught: ' . $e->getMessage());
//    var_dump($e);
}