<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\controller;

use ezmvc\lib\Request;
use ezmvc\lib\Response;
use ezmvc\lib as lib;

/**
 * Performs routing, dispatching and response rendering, depending on the request
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class FrontController extends BaseController {

    /**
     * Basic factory method
     * @return self
     */
    public static function factory() {
        return new self(Request::factory(), Response::factory());
    }

    /**
     * Routes the request to the correct actioncontroller
     */
    public function run() {

        // Parsing the request and setting up a route
        $this->_request->route();

        // Invoking requested controller
        $controller = $this->invokeController();

        $controller->Init();

        // Pre-dispatching the controller
        $controller->preDispatch();

        // Dispatching to the controller
        $this->dispatch($controller);

        // Send the response
        $this->_response->send();

        // Post-dispatching the controller
        $controller->postDispatch();
    }

    /**
     * Gets the controller from Request and returns a controller object
     * @return <type>
     */
    private function invokeController() {
        $controller = $this->_request->getController();
        return ActionController::factory($controller, $this->_request, $this->_response);
    }

    /**
     * Gets the action from Request and dispatches to the controller
     * @param ActionController $controller
     */
    private function dispatch(ActionController $controller) {
        $action = $this->_request->getAction();
        $id = $this->_request->getId();
        $params = $this->_request->getParams();
        $controller->$action($id, $params);
    }

}