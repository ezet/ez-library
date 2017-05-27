<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * Description of Isbndb
 *
 * @author Lars Kristian Dahl <http://www.krisd.com>
 */
class IsbnDb {

    private $_isbndbkey = 'S6UE9292';
    private $_apistring = 'http://isbndb.com/api/books.xml?';
    private $_response;
    private $_value;

    public function fetchByIsbn($value) {
        $xml = $this->fetch('isbn', $value);
        return $xml;
    }

    public function fetch($index, $value) {
        $value = '0061031321';
        $url = $this->_apistring . 'access_key=' . $this->_isbndbkey . '&index1=' . $index . '&value1=' . $value;
        $xml = simplexml_load_file($url);
        return $xml;
    }

}