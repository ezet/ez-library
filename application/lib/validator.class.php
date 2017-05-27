<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

use ezmvc\model\BaseModel;

/**
 * Performs basic validation
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Validator {
// TODO add sanitizing filters

    /**
     * The validation rules
     * @var <type> 
     */
    private $_rules = array();
    /**
     * The model to validate
     * @var <type>
     */
    private $_model;

    /**
     * Basic factory method
     * @param <type> $model
     * @return self
     */
    public static function factory($model=null) {
        return new self($model);
    }

    /**
     * Constructor
     * Adds a model if provided
     * @param BaseModel $model
     */
    public function __construct(BaseModel $model=null) {
        $this->addModel($model);
    }

    /**
     * Adds an error to the model
     * @param <type> $type
     * @param <type> $string
     */
    public function addError($type, $string) {
        $this->_model->addError($type, $string);
    }

    /**
     * Magic getter for properties in the model
     * @param <type> $var
     * @return <type>
     */
    public function __get($var) {
        return $this->_model->$var;
    }

    /**
     * Adds a model to the validator
     * @param <type> $model 
     */
    public function addModel($model) {
        if ($model) {
            $this->_model = $model;
            $this->_rules = $model->rules();
        }
    }

    /**
     * Performs the validation according to the $_rules
     * @param BaseModel $model
     */
    public function validate(BaseModel $model=null) {
        // last chance for adding a model
        $this->addModel($model);
        foreach ($this->_rules as $var => $validators) {
            foreach ($validators as $validator => $options) {
                // If options isnt an array, assume no options are sent and that it is actually the validator name
                if (!is_array($options)) {
                    $validator = $options;
                    $options = null;
                }
                // Ensure the validator exists
                if (is_callable(array($this, $validator))) {
                    $this->_varname = $var;
                    $this->$validator($var, $options);
                } else {
                    throw new \Exception('Invalid validator: ' . $validator);
                }
            }
        }
    }

    /**
     * Ensures the variable is not empty
     * @param <type> $var
     * @param <type> $options
     */
    private function required($var, $options) {
        if ($this->$var == '') {
            $this->addError($var, $var . ' cannot be empty.');
        }
    }

    /**
     * Ensures the variable is a string, and between min and/or max length if specified
     * @param <type> $var
     * @param <type> $options
     */
    private function string($var, $options) {
        $errormsg = '';
        $len = strlen($this->$var);
        if (!is_string($this->$var)) {
            $errormsg = $var . 'must be a string.';
        } elseif (isset($options['min']) && isset($options['max'])) {
            if ($len < $options['min'] || $len > $options['max'])
                $errormsg = $var . ' must be between ' . $options['min'] . ' and ' . $options['max'] . ' characters.';
        } elseif (isset($options['min'])) {
            if ($len < $options['min'])
                $errormsg = $var . ' must be longer than ' . $options['min'] . ' characters.';
        } elseif (isset($options['max'])) {
            if ($len < $options['max'])
                $errormsg = $var . ' must be shorter than ' . $options['max'] . ' characters.';
        }
        if ($errormsg)
            $this->addError($var, $errormsg);
    }

    /**
     * Ensures the variable is valid email format,
     * optional parameter to allow an empty field
     * @param <type> $var
     * @param <type> $options
     * @return <type>
     */
    private function email($var, $options) {
        $errmsg = 'Please provide a valid email.';
        // if email isnt reqired, return if email field is empty
        if (isset($options['required']) && !$options['required'])
            $errmsg = 'Please provide a valid or no email.';
        if (!$this->$var)
            return;
        if (!filter_var($this->$var, FILTER_VALIDATE_EMAIL)) {
            $this->addError($var, $errmsg);
        }
    }

    /**
     * Ensures the variable is valid URL format,
     * optional parameter to allow an empty field
     * @param <type> $var
     * @param <type> $options
     * @return <type>
     */
    private function url($var, $options) {
        $errmsg = 'Please provide a valid URL.';
        // if URL isnt reqired, return if url field is empty
        if (isset($options['required']) && !$options['required'])
            $errmsg = 'Please provide a valid or no URL.';
        if (!$this->$var)
            return;
        if (!filter_var($this->$var, FILTER_VALIDATE_URL)) {
            $this->addError($var, $errmsg);
        }
    }

    /**
     * Ensures the variable matches the value of a specified field
     * @param <type> $var
     * @param <type> $options
     */
    private function match($var, $options) {
        foreach ($options as $match) {
            if ($this->$var !== $this->$match) {
                $this->addError($var, $var . 's do not match.');
            }
        }
    }

    /**
     * Performs reCaptcha validation
     */
    private function captcha() {
        require_once(__APP_PATH . '/lib/recaptchalib.php');
        $privatekey = "6Ld58sESAAAAAMB16hcCtpSvlHVTmiH_P7_fYdTW";
        $resp = recaptcha_check_answer($privatekey,
                        $_SERVER["REMOTE_ADDR"],
                        $_POST["recaptcha_challenge_field"],
                        $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            $this->addError('captcha',
                    'The reCAPTCHA was not entered correctly.');
        }
    }

    /**
     * Ensures the file is is one of specified types, below specified size,
     * and that no other errors have occured.
     * @param <type> $var
     * @param <type> $options
     */
    private function file($var, $options) {
        $type = $_FILES['form']['type'][$var];
        $size = $_FILES['form']['size'][$var];
        $error = $_FILES['form']['error'][$var];
        $errormsg = '';
        if ($error) {
            $errormsg = 'An unexpected error has occured while uploading the file. (' . $error . ')';
        } elseif (isset($options['type']) && !in_array($type, $options['type'])) {
            $errormsg = 'Illegal file type. Files of type "' . $type . '" are not allowed.';
        } elseif (isset($options['size']) && $size > $options['size']) {
            $errormsg = 'Filesize is too large. Filesize cannot be larger than ' . $options['size'] . '.';
        }
        if ($errormsg) {
            $this->addError('file', $errormsg);
        }
    }

}