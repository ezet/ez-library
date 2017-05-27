<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\model;

use ezmvc\lib\Auth;

/**
 * Abstract base for all Domain Models
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
abstract class BaseModel {

    /**
     * The model properties
     * @var <type>
     */
    protected $_data = array();
    /**
     * Array of errors
     * @var <type>
     */
    protected $_errors = array();

    /**
     * Returns an array of properties allowed in the model
     * @var <type>
     */
    abstract public function validAttribs();

    /**
     * Returns a set of validation rules for the model
     */
    abstract public function rules();

    /**
     * Performs validation on the model according to the rules
     */
    abstract public function validate();

    /**
     * Prepares the model for persistent storage
     */
    abstract public function prepare();

    /**
     * Basic factory method for domain models
     * @param string $model
     * @param <type> $data
     * @return model
     */
    public static function factory($model, $data=null) {
        $model = __NAMESPACE__ . '\\' . $model;
        return (class_exists($model)) ? new $model($data) : null;
    }

    public static function get($data=null) {
        return new static($data);
    }

    /**
     * Initializes object with values from an array if provided
     * @param array $userdata
     */
    protected function __construct(array $data=null) {
        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * Magic getter for the data array
     * @param <type> $key
     * @return <type>
     */
    public function __get($name) {
        return (isset($this->_data[$name])) ? $this->_data[$name] : null;
    }

    /**
     * Magic setter for the value array, filters out invalid properties
     * @param <type> $key
     * @param <type> $value
     */
    public function __set($name, $value) {
        if (!in_array($name, $this->validAttribs())) {
            throw new \Exception("Invalid set property: $name");
        } else {
            $this->_data[$name] = $value;
        }
    }

    /**
     * Magic isset method for data array
     * @param <type> $name
     * @return <type>
     */
    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    /**
     * Magic unset method for data array
     * @param <type> $name
     */
    public function __unset($name) {
        unset($this->_data[$name]);
    }

    /**
     * Returns all the keys in the data array
     * @return <type>
     */
    public function getKeys() {
        return array_keys($this->_data);
    }

    /**
     * Returns the data array
     * @return <type>
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * Sets an array of data at once, filtered through the magic set method
     * @param array $data
     * @return BaseModel
     */
    public function setData(array $data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * Adds an error to the model, with type and an error message
     * @param <type> $type
     * @param <type> $error
     */
    public function addError($type, $error) {
        $this->_errors[$type] = $error;
    }

    /**
     * Returns the errors array
     * @return <type>
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Returns a specific error
     * @param <type> $name
     * @return <type>
     */
    public function getError($name) {
        return (isset($this->_errors[$name])) ? $this->_errors[$name] : null;
    }

}