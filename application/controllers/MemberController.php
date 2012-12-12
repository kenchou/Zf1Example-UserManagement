<?php
/**
 * User Controller
 *
 * @author Ken
 *
 */
class MemberController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $authUser = Zend_Auth::getInstance()->getIdentity();
            $id = $authUser->id;

            /* @var $user Application_Model_User */
            $user = $authUser->userModel;

            $roles = $user->fetchRoles();

            $this->view->user = $user;
            $this->view->roles = $roles;
        }
    }


    public function registerAction()
    {
        /* @var $form Zend_Form */
        $form = $this->_helper->form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            $username = $form->getValue('username');
            $password = $form->getValue('password');
            $data = $form->getValues();

            $userResource = $this->_helper->modelResource('Users');
            $user = $userResource->createModel();
            $user->populate($data);
Zend_Debug::dump($user);
            $user->save();

        }
        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = $this->_helper->form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $data = $form->getValues();

            $userResource = $this->_helper->modelResource('Users');
            $user = $userResource->createModel($data);

            //$user->save();

        }
        $this->view->form = $form;
    }
}

