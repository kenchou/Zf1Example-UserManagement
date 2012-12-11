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
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();

            return empty($user->realname) ? (empty($user->email) ? $user->username : $user->email) : $user->realname;
        }
        return null;
    }
}
