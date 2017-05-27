<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\view;

/**
 * A basic View class
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class View {

    private $_data = array();
    private $_template;
    private $_autorender = true;

    /**
     * Basic factory method
     * @param <type> $file
     * @return View
     */
    public static function factory($template=null) {
        return new View($template);
    }

    /**
     * Constructor
     * Stores the template file
     * @param <type> $file
     */
    public function __construct($template) {
        $this->_template = $template;
    }

    /**
     * Magic getter for view data
     * @param <type> $key
     * @return <type> 
     */
    public function __get($key) {
        return (isset($this->_data[$key])) ? $this->_data[$key] : null;
    }

    /**
     * Magic setter for view data
     * @param <type> $key
     * @param <type> $value
     */
    public function __set($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * Magic isset function for viewdata
     * @param <type> $key
     * @return <type>
     */
    public function __isset($key) {
        return isset($this->_data[$key]);
    }

    /**
     * Magic unset function for viewdata
     * @param <type> $key
     */
    public function __unset($key) {
        if (key_exists($key, $this->_data)) {
            unset($this->data[$key]);
        }
    }

    /**
     * Magic to string function, which renders the view
     * This is so you can simply echo a view object to render it
     * @return <type>
     */
    public function __toString() {
        return $this->render();
    }

    public function setTemplate($template) {
        $this->_template = $template;
    }

    /**
     * Assign an array of data to the view
     * @param array $arr
     */
    public function assign(array $arr) {
        $this->_data = array_merge_recursive($this->_data, $arr);
    }

    /**
     * Returns all the keys in the view data
     * @return <type>
     */
    public function getKeys() {
        $keys = array_keys($this->_data);
        return $keys ? $keys : false;
    }

    /**
     * Returns the values stored in the view data
     */
    public function getVars() {
        $values = array_values($this->_data);
        return $values ? $values : false;
    }

    /**
     * Returns the viewdata array
     * @return <type>
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * Clears the viewdata array
     */
    public function clearVars() {
        $this->_data = array();
    }

    /**
     * Renders the view with its template, extracts the viewdata into the local scope
     * and returns the buffered output
     * @param <type> $file
     * @return <type>
     */
    public function render() {
        $template = __APP_PATH . "/view/$this->_template.phtml";
        if (!file_exists($template)) {
            trigger_error('Could not render template:' . $template);
        }
        // extract data into local view, so we don't have to use $this->
        extract($this->_data);
        ob_start();
        include $template;
        return ob_get_clean();
    }

}