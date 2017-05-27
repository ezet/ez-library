<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\controller;

use \ezmvc\view\View;
use \ezmvc\lib\Request;
use \ezmvc\lib\Response;
use \ezmvc\lib\Auth;
use \ezmvc\lib\Config;
use ezmvc\lib\AuthException;

/**
 * Base for all ActionControllers
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
abstract class ActionController extends BaseController {

    protected $_pagetitle;
    /**
     * These variables hold the different views
     * @var <type>
     */
    protected $_layout;
    /**
     * Holds a authentication object
     * @var <type>
     */
    protected $_auth;

    /**
     * Factory method, enables dependency injection
     * @param <type> $controller
     * @param Request $request
     * @return controller
     */
    public static function factory($controller, Request $req, Response $res) {
        return new $controller($req, $res, Auth::factory());
    }

    /**
     * Constructor
     * Sets up the actioncontrollers for general use, concrete classes should
     * call this constructor if overriden
     * @param Request $request
     * @param Auth $user
     */
    public function __construct(Request $req, Response $res, Auth $auth) {
        parent::__construct($req, $res);
        $this->_auth = $auth;
    }

    public function Init() {
        // Sets some default values, these can be overriden
        if (!$this->_layout) {
            $this->_layout = Config::get('default_layout');
        }
        if ($this->_request->getFormat() != 'html') {
            $this->_layout = $this->_request->getFormat();
        }
        $this->_layout = View::factory('layout/' . $this->_layout);

        if (!$this->_pagetitle) {
            $this->_pagetitle = Config::get('default_pagetitle');
        }
        $this->pagetitle = $this->_pagetitle;

        $this->_response->setResponse($this->_layout);
    }

    /**
     * Magic getter for properties in the layout view
     * @param <type> $name
     * @return <type>
     */
    public function __get($name) {
        return (isset($this->_layout->$name)) ? $this->_layout->$name : null;
    }

    /**
     * Magic setter for properties in the layout view
     * @param <type> $name
     * @param <type> $value
     */
    public function __set($name, $value) {
        $this->_layout->$name = $value;
    }

    /**
     * This is called before every dispatch to a controller
     */
    abstract public function preDispatch();

    /**
     * This is called after every dispatch to a controller
     */
    abstract public function postDispatch();

    /**
     * This is the default method routed to by the frontcontroller
     */
    abstract public function actionIndex();

    /**
     * Sets the content view
     * @param <type> $view
     */
    protected function addView($view, $position='content') {
        if ($this->_request->getFormat() != 'html') {
            $view = $this->_request->getFormat() . '/' . $view;
        }
        $this->$position = View::factory($view);
    }

    protected function setLayout($layout) {
        $this->_layout->setTemplate('layout/' . $layout);
    }

    /**
     * Validates a filled form in relation to the model specified
     * @param <type> $form
     * @param <type> $model
     * @return <type>
     */
    protected function _validForm($form, $model) {
        return ($form != null && $model->SetData($form)->validate()) ? true : false;
    }

    /**
     * Checks if a user is logged in, and throws an exception if not
     * @return <type>
     */
    protected function _requireLogin() {
        if ($this->_auth->isLogged()) {
            return $this->_auth->getId();
        } else {
            throw new AuthException('You need to be logged in to perform this operation.');
        }
    }


    /**
     * Checks if an expression evaluates to true, throws a error if not
     * @param <type> $id
     */
    protected function _assert($expression) {
        // TODO add HttpErrorException class
        if (!$expression) {
            header("HTTP/1.1 404 Not Found");
            exit(1);
        }
    }

}