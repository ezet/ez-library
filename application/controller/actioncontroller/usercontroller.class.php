<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\controller\actioncontroller;

use ezmvc\lib as lib;
use ezmvc\dao\UserDao;
use ezmvc\model\User;
use ezmvc\view\View;

/**
 * ActionController for the ebbs User module.
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class UserController extends \ezmvc\controller\ActionController {

    protected $_pagetitle = 'Users';

    /**
     * Called before every dispatch to this controller
     */
    public function preDispatch() {

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
        $this->actionLogin();
       
    }

    /**
     * Register a new user
     */
    public function actionRegister() {

        if ($this->_auth->isLogged()) {
            $this->_redirect('library');
        }

        $user = User::get();
        // validate form and model
        if ($this->_validForm($this->_request->getPost('form'), $user)) {
            // prepare for persistence and insert
            $user->prepare();
            $user = UserDao::get()->insert($user);
            // log the user in and redirect
            $this->_auth->forceLogin($user);
            $this->_redirect('library/index/' . $user->UserId);
        }
        // prepare the view
        $this->addView('user/register', 'content');
        $this->pagetitle = 'Register';
        $this->content->user = $user;
    }

    /**
     * User login
     */
    public function actionLogin() {
        if ($this->_auth->isLogged()) {
            $this->_redirect('library');
        
        }
        // TODO clean up this action
        $loginform = \ezmvc\model\LoginForm::get();
        $formdata = $this->_request->getPost('form');
        // if form has been posted
        if ($formdata) {
            $loginform->setData($formdata);
            // validate/authenticate and take action accordingly
            $user = $loginform->validate();
            if ($user) {
                $this->_redirect('library');
            }
        }
        // prepare the view
        $this->addView('user/login', 'content');
        $this->pagetitle = "Log in";
        $this->content->form = $loginform;
    }

    /**
     * Show a users profile
     * @param <type> $userid
     */
    public function actionProfile($userid) {
//    TODO Add possibility to view by username

        $this->_assert($userid);
        $dao = UserDAO::get('User');
        $user = $dao->findById($userid);
        $this->_assert($user);
        // prepare the view
        $this->addView('user/profile', 'content');
        $this->pagetitle = 'User Profile';
        $this->content->user = $user;
    }

    public function actionEdit($id) {

         $id = $this->_auth->getId();
        $user = \ezmvc\dao\UserDAO::get()->findById($id);
        $this->username = $user->Username;
        $this->firstname = $user->FirstName;
        $this->lastname = $user->LastName;

        $userid = $this->_requireLogin();
        $dao = UserDao::get('');
        // populate the form
        $user = $dao->findById($userid);
        // if form is posted and valid
        if ($this->_validForm($this->_request->getPost('form'), $user)) {
            // prepare for persistence, update and redirect
            $user->prepare();
            $dao->update($user);
            $this->_redirect('library');
        }
        // prepare the view
        $this->addView('user/edit', 'content');
        $this->pagetitle = 'Edit profile';
        $this->content->user = $user;
    }

    /**
     * Log a user out
     */
    public function actionLogout() {
        $this->_auth->logout();
        // redirect to index
        $this->_redirect('');
    }

}