<?php
abstract class Application_Plugin_Acl_Adapter_Abstract
{
    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;
    /**
     * Set request object
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Zend_Controller_Plugin_Abstract
     */
    public function setRequest (Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        return $this;
    }
    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest ()
    {
        return $this->_request;
    }
    /**
     * Gets the acl role
     *
     */
    abstract public function getRole ();
    /**
     * Gets the acl resource
     *
     */
    abstract public function getResource ();
    /**
     * Gets the acl privilege or action
     *
     */
    abstract public function getPrivilege ();
    /**
     * This function is run when the request is not allowed.
     *
     */
    abstract public function notAllowed ();
}
