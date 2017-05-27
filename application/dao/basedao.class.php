<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\dao;

use ezmvc\lib\Database;
use ezmvc\model\User;

/**
 * Abstract base for all Data Access Objects
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
abstract class BaseDao {

    protected $_errors = array();
    /**
     * The database object
     * @var <type>
     */
    protected $_db;
    /**
     * The PDO object after connection has been established
     * @var <type>
     */
    protected $_dbh;
    /**
     * Which table we are working with
     * @var <type>
     */
    protected $_table;
    /**
     * Which model the current table is associated with
     * @var <type>
     */
    protected $_model;
    protected $_pkey;
    /**
     * The sql query we are building
     * @var <type>
     */
    protected $_sql;

    /**
     * Basic factory method, returns requested child object
     * @param <type> $table
     * @return <type>
     */
    public static function get() {
        return new static();
    }

    /**
     * Constructor
     * Stores the table we're working with, and the associated model
     * @param <type> $table
     */
    protected function __construct() {
        $this->_model = 'ezmvc\model\\' . $this->_model;
    }

    /**
     * Automatically detects which table we are working with by the name of the concrete child object
     * Also figures out which domain model to use based on the same object
     * Assumes a naming convention is followed, where the Model and Table are named the same,
     * and the database object prepends DAO to the table/model name.
     */
    protected function _detectTable() {
        $class = end(explode('\\', get_called_class()));
        $this->_table = str_replace('DAO', '', $class);
        $this->_model = __NAMESPACE__ . "\\$this->_table";
    }

    /**
     * Gets a new DB objects if none exists, and connects
     * @return <type>
     */
    protected function _connect() {
        // TODO move this to an execute function
        if (!$this->_dbh instanceof Database) {
            $this->_db = Database::factory();
            $this->_dbh = $this->_db->lazyConnect();
        }
        return $this->_dbh;
    }

    /**
     * Performs a lazy connect and prepares the PDO statement
     * @return <type>
     */
    protected function _prepare() {
        return $this->_connect()->prepare($this->_sql);
    }

    /**
     * Performs a raw query and returns the stmt handle
     * @param <type> $sql
     * @param array $params
     * @return <type>
     */
    protected function _rawQuery($sql, array $params=array()) {
        $this->_sql = $sql;
        $sth = $this->_prepare();
        return $sth->execute($params) ? $sth : false;
    }

    /**
     * Creates a new row with provided data
     * @param <type> $table
     * @param array $data
     * @return <type>
     */
    protected function _create(array $data) {
        $this->_sql = "INSERT INTO `$this->_table` ";
        $this->_values($data);
        $sth = $this->_prepare();
        return ($sth->execute($data)) ? $this->_dbh->lastInsertId() : false;
    }

    /**
     * Retrieves a single row, and returns the result as a domain model object
     * @param <type> $table
     * @param <type> $where
     * @param <type> $value
     * @param <type> $limit
     * @param <type> $offset
     * @param <type> $orderfield
     * @param <type> $order
     * @return <type>
     */
    protected function _retrieve($where='', $value='', $limit=1, $offset=0, $orderfield='', $order='') {
        $this->_select($this->_table);
        ($where && $value) ? $this->_where($where) : '';
        ($orderfield && $order) ? $this->_orderBy($orderfield, $order) : '';
        $this->_limit($limit, $offset);
        $sth = $this->_prepare();
        $sth->bindParam(':' . $where, $value);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->_model);
    }

    /**
     * Retrieves several rows, and returns them as an array of domain model objects
     * @param <type> $table
     * @param <type> $limit
     * @param <type> $offset
     * @param <type> $orderby
     * @param <type> $order
     * @return <type>
     */
    protected function _retrieveList($limit=20, $offset=0, $orderby='', $order='') {
        $this->_select($this->_table);
        ($orderby && $order) ? $this->_orderBy($orderby, $order) : '';
        $this->_limit($limit, $offset);
        $sth = $this->_prepare();
        if ($sth->execute()) {
            return $sth->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->_model);
        } else {
            return false;
        }
    }

    /**
     * Updates an entire row with values provided in an array
     * @param <type> $table
     * @param array $data
     * @param <type> $where
     * @param <type> $value
     */
    protected function _update($where, array $data) {
        $this->_sql = "UPDATE `$this->_table` SET ";
        $newdata = array();
        $value = $data[$where];
        unset($data[$where]);
        foreach ($data as $key => $val) {
            $newdata[] .= "`$key`='$val'";
        }
        $this->_sql .= ' ' . implode(', ', $newdata) . ' ';
        $this->_sql .= "WHERE `$where`=$value";
        return $this->_prepare()->execute();
    }

    /**
     * Increases a single field by 1
     * @param <type> $table
     * @param <type> $pkfield
     * @param <type> $pkey
     * @param <type> $field
     */
    protected function _updateCount($value, $field) {
        $this->_sql = "INSERT INTO $this->_table ($this->_pkey, $field) VALUES (:$this->_pkey, 1) ON DUPLICATE KEY UPDATE $field = $field+1";
        return $this->_prepare()->execute(array($this->_pkey => $value));
    }

    /**
     * Updates a single field
     * @param <type> $table
     * @param <type> $where
     * @param <type> $id
     * @param <type> $field
     * @param <type> $value
     */
    protected function _updateField($where, $id, $field, $value) {
        $this->_sql = "UPDATE `$this->_table` SET `$field` = '$value'";
        $this->_where($where);
        return $this->_prepare()->execute(array($where => $id));
    }

    /**
     * Performs a delete on specified table according to $field and $value
     * @param <type> $table
     * @param <type> $field
     * @param <type> $value
     */
    protected function _delete($where, $value) {
        $this->_sql = "DELETE FROM `$this->_table` WHERE `$where`=:$where";
        return $this->_prepare()->execute(array($where => $value));
    }

    /**
     * Returns the last inserted ID
     * @return <type>
     */
    public function lastInsertId() {
        return $this->_dbh->lastInsertId();
    }

    /**
     * Count result rows
     */
    protected function _countRows() {
        
    }

    /**
     * Parses an array into a VALUES query
     * @param <type> $data
     */
    protected function _values($data) {
        $keys = array_keys($data);
        $fields = '(' . implode(', ', $keys) . ' )';
        $bound = '(:' . implode(', :', $keys) . ')';
        $this->_sql .= $fields . ' VALUES ' . $bound;
    }

    /**
     * Adds a select statement
     * @param <type> $table
     */
    protected function _select($table) {
        $this->_sql = "SELECT * FROM `$table`";
    }

    /**
     * Adds a where clause
     * @param <type> $field
     */
    protected function _where($field) {
        $this->_sql .= " WHERE `$field`=:$field";
    }

    /**
     * Sets a result limit
     * @param <type> $limit
     * @param <type> $offset
     */
    protected function _limit($limit, $offset) {
        $this->_sql .= " LIMIT $offset, $limit";
    }

    /**
     * Add and clause
     * @param <type> $field
     * @param <type> $value
     */
    protected function _andClause($field, $value) {
        $this->_sql .= " AND `$field`=$value";
    }

    /**
     * Add order by
     * @param <type> $fieldname
     * @param <type> $order
     */
    protected function _orderBy($fieldname, $order='ASC') {
        $this->_sql .= " ORDER BY `$fieldname` $order";
    }

    protected function _in(Array $values) {
        $count = count($values);
        $values = array_fill(0, $count, '?');
        $this->_sql .= ' IN (' . implode(',', $values) . ')';
    }

    /**
     * Returns the tables primary key
     * @param <type> $table
     * @return <type>
     */
    public function getPrimaryKey($table) {
        try {
            $this->connect();
            // TODO get the db name from the config
            $dbname = 'ez_library';
            $this->_sql = "SELECT k.column_name
                    FROM information_schema.table_constraints t
                    JOIN information_schema.key_column_usage k
                    USING (constraint_name,table_schema,table_name)
                    WHERE t.constraint_type='PRIMARY KEY'
                    AND t.table_schema='$dbname'
                    AND t.table_name=:table";
            $sth = $this->_prepare();
            $sth->bindParam(':table', $table);
            $sth->execute();
            return $sth->fetchColumn(0);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

}