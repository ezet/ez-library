<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ezmvc\dao;

use ezmvc\model\Book;

class BookDao extends \ezmvc\dao\BaseDao {

    // Set these manually
    protected $_table = 'Book';
    protected $_model = 'Book';
    protected $_pkey = 'BookId';

    /**
     * Inserts a new blogpost, returns its ID
     * @param Blogpost $post
     * @return <type>
     */
    public function insert(Book $book) {
        $this->_insertCategory($book);
        $this->_insertTags($book);
        $data = $book->toArray();
        $id = parent::_create($data);
        return $this->lastInsertId();
    }

    public function findById($value) {
        $book = current(parent::_retrieve($this->_pkey, $value));
        $this->_getTagNames($book);
        $this->_getCategoryName($book);
        return $book;
    }

    public function findByUserId($value) {
        $res = parent::_retrieve('UserId', $value, 1000);
        array_walk($res, Array(__NAMESPACE__ . '\bookdao', '_getCategoryName'));
        array_walk($res, Array(__NAMESPACE__ . '\bookdao', '_getTagNames'));
        return $res;
    }

    public function findAll($limit=20, $offset=0) {
        $res = parent::_retrieveList($limit, $offset);
        array_walk($res, Array(__NAMESPACE__ . '\bookdao', '_getCategoryName'));
        array_walk($res, Array(__NAMESPACE__ . '\bookdao', '_getTagNames'));
        return $res;
    }

    public function update(Book $book) {
        $this->_insertCategory($book);
        $this->_InsertTags($book);
        $data = $book->toArray();
        return parent::_update($this->_pkey, $data);
    }

    public function delete(Book $book) {
        return parent::_delete($this->_pkey, $book->BookId);
    }

    private function _getCategoryName($book) {
        $table = $this->_table;
        $this->_table = 'Category';
        $cat = current(parent::_retrieve('CategoryId', $book->CategoryId));
        $this->_table = $table;
        $book->CategoryName = $cat->CategoryName;
        return $book;
    }

    private function _insertCategory($book) {
        if ($book->CategoryName && !$book->CategoryId) {
            $table = $this->_table;
            $this->_table = 'Category';
            $res = current(parent::_retrieve('CategoryName', $book->CategoryName));
            if ($res) {
                $book->CategoryId = $res->CategoryId;
            } else {
                parent::_create(Array('CategoryName' => $book->CategoryName));
                $book->CategoryId = $this->lastInsertId();
            }
            $this->_table = $table;
        }
        unset($book->CategoryName);
        return $book;
    }

    private function _getTagNames($book) {
        $tags = explode(',', $book->Tags);
        $this->_sql = 'SELECT TagName FROM Tag WHERE TagId';
        $this->_in($tags);
        $sth = $this->_prepare();
        $sth->execute($tags);
        $res = $sth->fetchAll(\PDO::FETCH_NUM);
        $tags = Array();
        foreach ($res as $tag) {
            $tags[] = $tag[0];
        }
        $book->Tags = implode(', ', $tags);
        return $book;
    }

    private function _insertTags($book) {
        if ($book->Tags) {
            $this->_table = 'Tag';
            $tags = explode(',', $book->Tags);
            $tagids = Array();
            foreach ($tags as $tag) {
                $res = current(parent::_retrieve('TagName', $tag));
                if ($res) {
                    $tagids[] = $res->TagId;
                } else {
                    parent::_create(Array('TagName' => $tag));
                    $tagids[] = $this->lastInsertId();
                }
            }
            $book->Tags = implode(',', $tagids);
            $this->_table = 'Book';
        }
        return $book;
    }
}