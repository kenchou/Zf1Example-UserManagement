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

    public function loginAction()
    {
        /* @var $form Zend_Form */
        $form = $this->_helper->form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $staticSalt = 'a;dsoifhsaklg';

            $username = $form->getValue('username');
            $password = $form->getValue('password');

            $db = Zend_Db_Table::getDefaultAdapter();

            //var_dump($db);exit;
            $authAdapter = new Zend_Auth_Adapter_DbTable($db);
            //we can use chain
            $authAdapter->setTableName('users')
                        ->setIdentityColumn('username')->setIdentity($username)
                        ->setCredentialColumn('password')->setCredential($password)
                        ->setCredentialTreatment("UNHEX(MD5(CONCAT('$staticSalt', ?, salt)))")
            ;

            //Zend_Debug::dump($authAdapter);
            $select = $authAdapter->getDbSelect();
            $select->where('status = ?', 0);

            $auth = Zend_Auth::getInstance();
            $rs = $auth->authenticate($authAdapter);

            if ($rs->isValid()) {
               /*  $storage = $auth->getStorage();
                $storage->write($authAdapter->getResultRowObject(array(
                        'username',
                        'real_name',
                ))); */

                $authUser = $auth->getIdentity();
                var_dump($authUser);
            } else {
                $form->getElement('password')->addError('Login information incorrect. Please try again.');
                $form->markAsError();
            }
        }

        $this->view->form = $form;
    }

    public function logoutAction()
    {

    }

    public function registerAction()
    {
        $form = $this->_helper->form();
        $this->view->form = $form;
    }

    public function profileAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $storage = Zend_Auth::getInstance()->getStorage();
            $session = new Zend_Session_Namespace($storage->getNamespace());
            $user = $session->user;
            $this->view->user = $user;
        }
    }
}

