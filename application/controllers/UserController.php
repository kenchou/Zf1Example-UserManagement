<?php
/**
 * User Controller
 *
 * @author Ken
 *
 */
class UserController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function listAction ()
    {
        $userResource = $this->_helper->modelResource('Users');
        $users = $userResource->fetchAll();
        $this->view->users = $users;
    }

    public function addAction()
    {
        /* @var $form Zend_Form */
        $form = $this->_helper->form('register');
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            $username = $form->getValue('username');
            $password = $form->getValue('password');
            $data = $form->getValues();

            $userResource = $this->_helper->modelResource('Users');
            $user = $userResource->createModel($data);

            $user->save();

        }
        $this->view->form = $form;
    }

    public function setStatusAction()
    {
        $id = $this->_getParam('id');
        $status = $this->_getParam('status');

        $userResource = $this->_helper->modelResource('Users');
        $user = $userResource->find($id)->getIterator()->current();

        $user->status = $status;
        $user->save();

        $this->_helper->viewRenderer->setNoRender();

        $this->_helper->redirector('list');
    }

}

