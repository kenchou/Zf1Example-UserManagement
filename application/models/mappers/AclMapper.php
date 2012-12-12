<?php
/**
 * ACL: Access control list
 * @author Ken
 *
 */
class Application_Model_Mapper_Acl extends Application_Model_Mapper_MapperAbstract
{
    protected $_defaultModelClass = 'AclRule';
    protected $_defaultDbTableClass = 'Acl';
    protected $_colsMap = array(
        'id' => 'acl_id',
        'roleId' => 'role_id',
        'roleName' => 'role_name',
        'resourceId' => 'resource_id',
        'resourceName' => 'resource_name',
        'action' => 'action',
        'permit' => 'permit',
    );

    public function fetchRowByName($name)
    {
        $select = $this->getDbTable()->select();
        $select->where('role_name=?', $name);
        return $this->fetchRow($select);
    }

    public function fetchRowByRoleAndResource($roleId, $resourceId, $action = null)
    {
        $select = $this->getDbTable()->select();
        $select->where('role_id=?', $roleId)
               ->where('resource_id=?', $resourceId);
        if (null !== $action) {
            $select->where('action=?',$action);
        }
        return $this->fetchRow($select);
    }

    public function cleanByRoleAndResource($roleId, $resourceId, $action = null)
    {
        $select = $this->getDbTable()->select();
        $select->where('role_id=?', $roleId)
               ->where('resource_id=?', $resourceId);
        if (null !== $action) {
            $select->where('action=?',$action);
        }
        $rows = $this->getDbTable()->fetchAll($select);
        foreach ($rows as $row) {
            $row->delete();
        }
        return $this;
    }

    public function fetchAllByRole($role)
    {
        $table = $this->getDbTable();
        $select = $table->select()->setIntegrityCheck(false);
        $select->join(array('s'=>'Application_resources', ''));
        if (is_array($role)) {
            $select->where('role_id IN(?)', $role);
        } else {
            $select->where('role_id=?', $role);
        }
        return $this->fetchAll($select);
    }
}