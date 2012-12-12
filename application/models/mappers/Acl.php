<?php
/**
 * ACL: Access control list
 * @author Ken
 *
 */
class Application_Model_Mapper_Acl extends Application_Model_Mapper_MapperAbstract
{
    protected $_modelClass = 'Application_Model_AclRule';
    protected $_dbTableClass = 'Application_Model_DbTable_Acl';
    protected $_dbTableName = 'Acl';

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

    /**
     * find Acl rules by role id
     * @param unknown $role
     * @return Ambigous <Ambigous, multitype:, Application_Model_ModelCollection, Application_Model_ModelAbstract>
     */
    public function findByRole($role)
    {
        $table = $this->getDbTable();
        $select = $this->getSqlSelect()->setIntegrityCheck(false);
        $select->from(array('i'=> $table->info('name')));
        $select->join(array('s'=>'resources'), 's.id=i.resource_id');
        if (is_array($role)) {
            $select->where('role_id IN(?)', $role);
        } else {
            $select->where('role_id=?', $role);
        }

        return $this->fetchAll($select);
    }
}