<?php
/**
 * User Controller
 *
 * @author Ken
 *
 */
class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $sessionUser = Zend_Auth::getInstance()->getIdentity();
            $id = $sessionUser->id;
            $resource = $this->_helper->modelResource('UserMapper');
            $user = $resource->find($id)->getIterator()->current();

            $this->view->user = $user;
        }
    }

    public function editAction()
    {
        $form = $this->_helper->form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $data = $form->getValues();

            $userResource = $this->_helper->modelResource('UserMapper');
            $user = $userResource->createModel($data);

            //$user->save();

        }
        $this->view->form = $form;
    }
}

