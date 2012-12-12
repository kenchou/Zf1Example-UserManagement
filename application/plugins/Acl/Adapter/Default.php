<?php

class Application_Plugin_Acl_Adapter_Default extends Application_Plugin_Acl_Adapter_Abstract
{
    /**
     * Gets the acl role
     *
     */
    public function getRole ()
    {
        $authUser = Zend_Auth::getInstance()->getIdentity();
        if (is_object($authUser)) {
            return $authUser->username;
        }
        return $authUser;
    }

    /**
     * Gets the acl resource
     *
     */
    public function getResource ()
    {
        $request = $this->_request;
        $module = $request->getModuleName();

        $resource = '';
        if ($module != 'default') {
            $resource = $this->_format($module) . '_';
        }
        return $resource . $this->_format($request->getControllerName());
    }

    /**
     * Gets the acl privilege or action
     *
     */
    public function getPrivilege ()
    {
        $string = $this->_format($this->_request->getActionName());
        $string{0} = strtolower($string{0});
        return $string;
    }

    /**
     * This function is run when the request is not allowed.
     *
     */
    public function notAllowed ()
    {
        $this->_request->setActionName('forbidden')->setControllerName('error')->setModuleName('default')->setDispatched(false);
    }

    /**
     * format resource name
     * @param string $name
     */
    protected function _format($name)
    {
        return str_replace(' ', '', lcfirst(str_replace('-', ' ', $name)));
    }
}
