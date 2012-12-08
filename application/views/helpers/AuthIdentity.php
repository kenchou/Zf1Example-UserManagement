<?php
/**
 *
 * @author ken
 * @version $SVN:Id$
 */

/**
 * AuthIdentity helper
 *
 */
class Application_View_Helper_AuthIdentity extends Zend_View_Helper_Abstract
{
    /**
     * get user identity
     * @return mixed
     */
    public function authIdentity()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $storage = Zend_Auth::getInstance()->getStorage();
            $session = new Zend_Session_Namespace($storage->getNamespace());
            $user = $session->user;
            return $user->nickname ? $user->nickname : (empty($user->email) ? $user->fullname : $user->email);
        }
        return null;
    }
}
