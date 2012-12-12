<?php
/**
 * System reosurce for ACL
 * @author Ken
 *
 */
class Application_Model_Resource extends Application_Model_ModelAbstract
{
    protected $_defaultResourceName = 'Resources';
    protected $_mapperClass = 'Application_Model_Mapper_Resources';

	public function getAcls()
	{
		$roles = $this->getRoles();
		foreach ($roles as $role) {
			$items = $role->getAcl();
		}
	}

	public function __toString()
	{
		return $this->name;
	}

}