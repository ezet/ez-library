<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\controller\actioncontroller;

/**
 * Example controller.
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class IndexController extends \ezmvc\controller\ActionController {

    /**
     * This is called before every dispatch to this controller
     */
    public function preDispatch() {
        // set the default style when viewing blogs
        // Using this simple method, you can also change the whole layout of the page
        // See ActionController::$_template that gets rendered as a view
    }

    /**
     * This is called after every dispatch to this controller
     */
    public function postDispatch() {

    }

    /**
     * This is the default method routed to by the frontcontroller
     */
    public function actionIndex() {
        $this->addView('index/index');
    }



}