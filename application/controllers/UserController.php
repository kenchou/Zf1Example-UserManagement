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
            $user = $userResource->createModel($data);

            $user->save();

        }
        $this->view->form = $form;
    }
}

