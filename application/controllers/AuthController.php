<?php
/**
 * Auth Controller
 * login/logout
 * @author Ken
 *
 */
class AuthController extends Zend_Controller_Action
{
    public function loginAction()
    {
        /* @var $form Zend_Form */
        $form = $this->_helper->form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $staticSalt = Application_Model_User::$staticSalt;

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
                $storage = $auth->getStorage();
                $authInfo = $authAdapter->getResultRowObject(array(
                    'id',
                    'username',
                    'realname',
                    'email',
                ));
                $userResource = $this->_helper->modelResource('Users');
                $user = $userResource->find($authInfo->id)->getIterator()->current();
                $authInfo->userModel = $user;    //attach model to session

                $storage->write($authInfo);

                $authUser = $auth->getIdentity();

                $this->_helper->redirector('index', 'user');
            } else {
                foreach ($rs->getMessages() as $message) {
                    $form->getElement('password')->addError($message);
                }

                $form->markAsError();
            }
        }

        $this->view->form = $form;
    }

    public function logoutAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::expireSessionCookie();
    }
}

