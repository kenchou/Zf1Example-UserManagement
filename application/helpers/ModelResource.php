<?php
/**
 *model Resource Action Helper
 *
 * @author Ken
 * @version
 */
class Application_Controller_Action_Helper_ModelResource extends Zend_Controller_Action_Helper_Abstract
{
    protected $_config;
    protected $_loader;

    public function __construct()
    {
        $this->_loader = Zend_Loader_Autoloader::getInstance();
    }


    /**
     * Strategy pattern: call helper as broker method
     *
     * @param string $name
     * @param array $options
     * @return Application_Model_Mapper_MapperAbstract
     */
    public function direct($name, $type = 'mappers')
    {
        return $this->load($name, $type);
    }

    /**
     *
     * @param string $name
     * @param array $options
     * @return Application_Model_Mapper_MapperAbstract
     */
    public function load($name, $type = 'mappers')
    {
        $name = ucfirst($name);
        $namespace = "Application_Model_Mapper_";
        $class = $namespace . $name;

        $resourceLoaders = $this->_loader->getClassAutoloaders($class);

        foreach ($resourceLoaders as $loader) {
            return $loader->load($name, $type);
        }
    }
}
