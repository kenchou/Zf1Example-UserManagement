<?php
class Application_Model_Role extends Application_Model_ModelAbstract
{
    protected $_defaultResourceName = 'Roles';
    protected $_mapperClass = 'Application_Model_Mapper_Roles';

    protected $_properties = array(
        'id',
        'name',
        'description',
    );
    protected $_resources;
    protected $_acl;

	public function getAcl()
	{
		return $this->getMapper()->getAcl($this);
	}

	public function getResources()
	{
		if (empty($this->_resources)) {
			$this->_resources = $this->getMapper()->getResources($this);
			foreach ($this->_resources as $resource) {
				$this->_acl[$resource->id] = $resource->id;
			}
		}
		return $this->_resources;
	}

	public function __toString()
	{
		return $this->name;
	}

	public function __sleep()
	{
		return array('_properties', '_data', '_resources', '_acl');
	}
}