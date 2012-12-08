<?php
/**
 *
 * @author Ken
 * @version
 */
/**
 * Merchants Resource Action Helper
 *
 * @uses actionHelper Application_Controller_Action_Helper
 */
class Application_Controller_Action_Helper_Form extends Zend_Controller_Action_Helper_Abstract
{
    protected $_config;

    public function __construct()
    {
        $configFile = APPLICATION_PATH . '/configs/forms.yml';
        $this->_config = new Zend_Config_Yaml($configFile);
    }

    /**
     * Strategy pattern: call helper as broker method
     *
     * @param string $name
     * @param array $options
     * @return Zend_From
     */
    public function direct($name = null)
    {
        return $this->getForm($name);
    }

    /**
     *
     * @param string $name
     * @param array $options
     * @return Zend_From
     */
    public function getForm($name = null)
    {
        if (empty($name)) {
            $name = $this->getRequest()->getActionName();
        }
        $p = explode('.', $name);
        if (count($p)>1) {
            $controller = $p[0];
            $name = $p[1];
        } else {
            $controller = $this->getRequest()->getControllerName();
        }
        if (empty($this->_config->$controller->$name)) {
            throw new Exception("$controller.$name not found in config");
        }

        $form = new Zend_Form();

        $form->setConfig($this->_config->$controller->$name);

        if ($this->_config->$controller->$name->action) {
        	$action = strtr($this->_config->$controller->$name->action, array('{action}' => $this->getRequest()->getActionName() , '{controller}' => $controller , '{module}' => $this->getRequest()->getModuleName()));
            $action = $this->getRequest()->getBaseUrl() . $action;
            $form->setAction($action);
        }


        return $form;
    }
}
