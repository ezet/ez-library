<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\model;

use ezmvc\lib\Validator;
use ezmvc\lib\Config;
use ezmvc\lib\Auth;

/**
 * Class for managing users
 *
 * @author Lars Kristian Dahl <http://www.krisd.com>
 */
class User extends \ezmvc\model\BaseModel {

    /**
     * Defines valid model attributes
     * @return <type>
     */
    public function validAttribs() {
        return array(
            'UserId',
            'Username',
            'Password',
            'ConfirmPassword',
            'FirstName',
            'LastName',
            'Email',
            'WebUrl',
            'About',
            'Template',
            'LoginCount',
            'LastLogin',
            'Created',
            'Modified',
            'Blocked',
            'FlaggedCount'
        );
    }

    /**
     * Defines the validation rules
     * @return <type>
     */
    public function rules() {
        return array(
            'Username' => array('string' => array('min' => 4, 'max' => 15), 'required'),
            'Password' => array('match' => array('ConfirmPassword'), 'string' => array('min' => 8, 'max' => 20), 'required'),
            'FirstName' => array('string', 'required'),
            'LastName' => array('string', 'required'),
            'Email' => array('email'),
//            'WebUrl' => array('url' => array('required' => 0)),
//            'Template' => array('required'),
//            'ProfileImage' => array('file' => array('type' => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'), 'size' => 200000))
        );
    }

    /**
     * Performs model validation
     * @return <type>
     */
    public function validate() {
        Validator::factory($this)->validate();
        return!$this->getErrors();
    }

    /**
     * Prepares the model for persistence
     */
    public function prepare() {
        if (!isset($this->Created))
            $this->Created = date('YmdHis', time());

        if (isset($this->ConfirmPassword))
            unset($this->ConfirmPassword);

        $this->Password = Auth::getHash($this->Password);
//        $this->ProfileImage = $this->_processProfileImage();
    }
}