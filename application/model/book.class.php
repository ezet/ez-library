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
 * Entity class for books 
 *
 * @author Lars Kristian Dahl <http://www.krisd.com>
 */
class Book extends \ezmvc\model\BaseModel {

    /**
     * Defines valid model attributes
     * @return <type>
     */
    public function validAttribs() {
        return array(
            'BookId',
            'UserId',
            'Isbn',
            'Cover',
            'Title',
            'Author',
            'Publisher',
            'DatePublished',
            'Synopsis',
            'Tags',
            'TagId',
            'TagName',
            'CategoryId',
            'CategoryName',
            'DateAdded',
            'Rating',
            'Review'
        );
    }

    /**
     * Defines the validation rules
     * @return <type>
     */
    public function rules() {
        return array(
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
        if (!isset($this->DateAdded))
            $this->DateAdded = date('YmdHis', time());
    }

}