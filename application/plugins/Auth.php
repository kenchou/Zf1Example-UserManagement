<?php

/**
 * Auth Plugin
 *
 */
class Application_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Log
     */
    protected $_logger;

    public function getLogger()
    {
        if (null === $this->_logger) {
            $this->_logger = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('log');
        }
        return $this->_logger;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();

        $controller = $request->getControllerName();
        $action = $request->getActionName();

        switch ($controller) {
            case 'auth':
            case 'index':
            case 'error':
                return;
            case 'user':
                $action = $request->getActionName();
                if (in_array($action, array('index'))) {
                    return;
                }
                break;
            case 'member':
                $action = $request->getActionName();
                if (in_array($action, array('register'))) {
                    return;
                }
                break;
            default:
                break;
        }

        // Page authentication
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($user == null) {
            //redirect
            $this->getLogger()->debug('need login. forward to /auth/login');
            $request->setModuleName('default')
                    ->setControllerName('auth')
                    ->setActionName('login')
                    ->setDispatched(false);
    	}
    }
}