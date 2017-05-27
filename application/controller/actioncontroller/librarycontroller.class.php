<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\controller\actioncontroller;

use ezmvc\lib as lib;
use ezmvc\dao\BookDao;
use ezmvc\model\BaseModel;
use ezmvc\view\View;

/**
 * ActionController for the ebbs User module.
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class LibraryController extends \ezmvc\controller\ActionController {

    protected $_pagetitle = 'MyLibrary';
    protected $_layout = 'library';

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

    /**
     * This is the default method routed to by the frontcontroller
     */
    public function actionIndex() {
        $this->addView('library/index');
        $books = BookDao::get()->findAll();
        $id = $this->_auth->getId();
        $user = \ezmvc\dao\UserDAO::get()->findById($id);
        $this->username = $user->Username;
        $this->firstname = $user->FirstName;
        $this->lastname = $user->LastName;
    }



}