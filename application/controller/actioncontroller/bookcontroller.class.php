<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ezmvc\controller\actioncontroller;

use ezmvc\lib as lib;
use ezmvc\dao\BookDao;
use ezmvc\model\Book;
use ezmvc\view\View;

class BookController extends \ezmvc\controller\ActionController {

    protected $_pagetitle = 'Book';

    /**
     * Called before every dispatch to this controller
     */
    public function preDispatch() {
        $this->_requireLogin();
    }

    /**
     * Called after every dispatch to the controller
     */
    public function postDispatch() {
        
    }

    public function actionIndex() {
//        $this->actionGet();
    }

    /**
     * Emulates a RESTful GET request, returns all bookss
     */
    public function actionGet() {
        $this->addView('book/list');
        $this->content->books = BookDAO::get()->findAll();
    }

    /**
     * Finds all books owned by a logged in user
     */
    public function actionGetByUser() {
        $this->addView('book/list');
        $books = BookDao::get()->findByUserId($this->_auth->GetId());
        foreach ($books as $book) {
            unset($book->Synopsis);
            unset($book->Review);
            unset($book->Tags);
        }
        $this->content->books = $books;
    }

    /**
     * Emulates a RESTful POST request, inserts a new book
     */
    public function actionPost() {
        $book = Book::get();
        $retval = 'Invalid data.';
        if ($this->_validForm($this->_request->getPost('form'), $book)) {
            $book->UserId = $this->_auth->getId();
            $book->prepare();
            $retval = BookDAO::get()->insert($book);
            if (!$retval) {
                $retval = 'An error has occured, row could not be inserted.';
            }
        }
        echo $retval;
    }

    /**
     * Emulates a RESTful PUT request, updates a book
     */
    public function actionPut() {
        $data = $this->_request->getPost();
        $book = Book::get();
        $book->BookId = $data['id'];
        $this->_requireOwner($book->BookId);
        $book->$data['columnName'] = $data['value'];
        if (BookDao::get()->update($book)) {
            echo $this->_request->getPost('value');
        } else {
            echo 'An error has occured while updating, please try again.';
        }
    }

    /**
     * Emulates a RESTful DELETE request, deletes a book
     */
    public function actionDelete() {
        $book = Book::get();
        $book->BookId = $this->_request->getPost('id');
        $this->_requireOwner($book->BookId);
        if (BookDao::get()->delete($book)) {
            echo 'ok';
        } else {
            echo 'An error has occured.';
        }
    }

    /**
     * Fetches book data by an ISBN identifier
     * @param <type> $isbn
     */
    public function actionFind($isbn) {
        $this->addView('book/list', 'content');
        $bookfinder = new \ezmvc\lib\GBook;
        $gbook = $bookfinder->findByIsbn($isbn);
        $book = Book::get();
        $book->Isbn = $gbook['isbn'];
        $book->Title = $gbook['title'];
        $book->Author = $gbook['author'];
        $book->Publisher = $gbook['publisher'];
        $book->DatePublished = $gbook['datePublished'];
        $book->Synopsis = $gbook['synopsis'];
        $book->CategoryName = isset($gbook['category'][0]) ? $gbook['category'][0] : '';
        $book->Tags = (is_array($gbook['category'])) ? implode(',', $gbook['category']) : '';
        $books[] = $book;
        $this->content->books = $books;
    }

    /**
     * Fetches tooltip information
     * @param <type> $bookId
     */
    public function actionTooltip($bookId) {
        $this->_requireOwner($bookId);
        $this->addView('book/list');
        $book = BookDao::get()->findById($bookId);
        $tooltip = Book::get();
        $tooltip->Synopsis = $book->Synopsis;
        $this->content->books = Array($tooltip);
    }

    /**
     * Fetches book details
     * @param <type> $bookId
     */
    public function actionDetails($bookId) {
        $this->_requireOwner($bookId);
        $this->addView('book/list');
        $book = BookDao::get()->findById($bookId);
        $details = Book::get();
        $details->Synopsis = $book->Synopsis;
        $details->Review = $book->Review;
        $details->Tags = $book->Tags;
        $this->content->books = Array($details);
    }

    /**
     * Checks if a user is logged in as the owner of the specified book entry
     * and throws an exception if not.
     * @param <type> $table
     * @param <type> $postid
     * @return <type>
     */
    private function _requireOwner($id) {
        $userid = $this->_requireLogin();
        $book = BookDao::get()->findById($id);
        if ($book->UserId !== $userid) {
            throw new lib\AuthException('You need to be logged in as the owner to perform this operation.');
        }
    }

}