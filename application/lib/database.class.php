<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

use ezmvc\lib\Config;

/**
 * PDO connection manager
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Database {

    /**
     * Holds a reference to the PDO handle
     * Since its static, only one PDO object can be referenced
     * @var <type>
     */
    protected static $_dbh;
    /**
     * Holds the complete dsn string, set on construction
     * @var <type>
     */
    protected $_dsn;

    /**
     * Compiles the DSN string from a provided array, or from Config if none is given
     * @param array $dbinfo
     */
    public function __construct(array $dbinfo=null) {
        if (!$dbinfo) {
            $dbinfo = $this->_dbinfo = Config::get('dbinfo');
        }
        $type = $dbinfo['type'];
        $host = $dbinfo['host'];
        $port = $dbinfo['port'];
        $dbname = $dbinfo['dbname'];
        $this->_dsn = "$type:host=$host;port=$port;dbname=$dbname;";
    }

    /**
     *  Basic factory method
     * @param array $dbinfo
     * @return self
     */
    public static function factory(array $dbinfo=null) {
        return new self($dbinfo);
    }

    /**
     * Performs lazy connection, only connecting to the DB when a query is actually performed
     * Also ensures that only 1 connection can be active, by returning the existing PDO object if one exists
     * @return <type>
     */
    public function lazyConnect() {
        if (!self::$_dbh instanceof \PDO) {
            try {
                self::$_dbh = new \PDO($this->_dsn, $this->_dbinfo['username'], $this->_dbinfo['password']);
                self::$_dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                die('Could not connect to database');
            }
        }
        return self::$_dbh;
    }

    /**
     * Disconnects from the database
     */
    public function disconnect() {
        
    }

}