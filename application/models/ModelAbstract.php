<?php
/**
 * Model Abstract
 * use Traits in php 5.4
 * @author Ken
 *
 */
class Application_Model_ModelAbstract extends ArrayObject
{
    protected $_mapperNamespace = 'Application_Model_Mapper';
    protected $_defaultResourceName;
    protected $_mapperClass;
    protected $_properties = array();

    public function __construct($array = array())
    {
        parent::__construct($array, ArrayObject::STD_PROP_LIST+ArrayObject::ARRAY_AS_PROPS);
    }

    public function loadResource($name, $type = 'mappers')
    {
        $name = ucfirst($name);
        $class = $this->_mapperNamespace . '_' . $name;

        $resourceLoaders = Zend_Loader_Autoloader::getInstance()->getClassAutoloaders($class);
        foreach ($resourceLoaders as $loader) {
            return $loader->load($name, $type);
        }
    }

    /**
     * get mapper resource
     * @param string $resourceName
     * @return Application_Model_Mapper_MapperAbstract
     */
    public function getMapper($resourceName = null)
    {
        if (null === $resourceName) {
            $resourceName = $this->_defaultResourceName;
        }
        $resource = $this->loadResource($resourceName);
        return $resource;
    }

    public function populate($data = array())
    {
        foreach ($data as $k => $v) {
            $this[$k] = $v;
        }
        return $this;
    }

    public function toArray()
    {
        return $this->getArrayCopy();
    }

    public function save()
    {
        $resource = $this->getMapper();
        $resource->save($this);
        return $this;
    }
}