<?php
/**
 * User Controller
 *
 * @author Ken
 *
 */
class AclController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $this->_forward('list');
    }

    public function listAction ()
    {
        $roleId = $this->_getParam('roleId');
        $resourceId = $this->_getParam('resourceId');

        $aclResource = $this->_helper->modelResource('Acl');
        $aclRules = $aclResource->findDetail($roleId, $resourceId);
        $this->view->aclRules = $aclRules;
    }

    public function addAction()
    {
        /* @var $form Zend_Form */
        $form = $this->_helper->form('edit');
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
        $this->render('edit');
    }
}

