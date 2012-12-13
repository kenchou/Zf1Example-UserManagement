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
        'id'           => 'acl_id',
        'roleId'       => 'role_id',
        'resourceId'   => 'resource_id',
        'action'       => 'action',
        'permit'       => 'permit',
        'roleName'     => 'rolename',
        'resourceName' => 'resource_name',
    );

    public function findDetail($roleId = null, $resourceId = null, $action = null)
    {
        $table = $this->getDbTable();
        $select = $this->getSqlSelect()->setIntegrityCheck(false);
        $select->from(array('i' => $table->info('name')))
               ->join(array('ro' => 'roles'), 'i.role_id=ro.id')
               ->join(array('rs' => 'resources'), 'i.resource_id=rs.id');
        if ($roleId) {
            $select->where('role_id=?', $roleId);
        }
        if ($resourceId) {
            $select->where('resource_id=?', $resourceId);
        }
        return $this->fetchAll($select);
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