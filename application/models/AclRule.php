<?php
/**
 * ACL rule
 * @author Ken
 *
 */
class Application_Model_AdminAclRule extends Application_Model_ModelAbstract
{
    protected $_defaultResourceName = 'AclMapper';
    protected $_mapperClass = 'Application_Model_Mapper_AclMapper';

    protected $_properties = array(
        'id',
        'roleId',
        'roleName',
        'resourceId',
        'resourceName',
        'action',
        'permit',
    );

	public function getAcl()
	{
		return $this->getMapper()->getAcl($this);
	}

	public function getResources()
	{
		return array();
		if (empty($this->_resources)) {
			$this->_resources = $this->getMapper()->getResources($this);
			foreach ($this->_resources as $role) {
				$this->_roleIds[$role->id] = $role->id;
			}
		}
		return $this->_resources;
	}

	public function __toString()
	{
		return $this->name;
	}
}