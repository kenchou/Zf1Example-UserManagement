<?php
/**
 * ACL Role
 * @author Ken
 *
 */
class Application_Model_Mapper_RoleMapper extends Application_Model_Mapper_MapperAbstract
{
    protected $_modelClass = 'Application_Model_Role';
    protected $_dbTableNamespace = 'Application_Model_DbTable';
    protected $_dbTableClass = 'Application_Model_DbTable_Roles';
    protected $_dbTableName = 'Roles';

    protected $_colsMap = array(
        'id' => 'id',
        'name' => 'rolename',
        'description' => 'description',
    );

    public function getAcl($role)
    {
        $data = $this->_modelToCols($role);
        $row = $this->_fetchRowOrCreate($data);

        $table = $this->getDbTable();
        $db = $table->getAdapter();
        $select = $table->select()->setIntegrityCheck(false);
        $select->from(array('r'=>$table->info('name'), 'role_name'))
               ->join(array('i'=> 'Application_acl'), $db->quoteInto('i.role_id=r.role_id AND r.role_id=?', $role->id))
               ->join(array('s' => 'Application_resources', 'resource_name'), 'i.resource_id=s.resource_id');
        $rowset = $table->fetchAll($select);
        $roleMapper = new Application_Model_Mapper_Acl();
        $result = $roleMapper->createCollection($rowset);

        return $result;
    }

    public function getResources($role)
    {
        $data = $this->_modelToCols($role);
        $row = $this->_fetchRowOrCreate($data);
        $rowset = $row->findManyToManyRowset('Application_Model_DbTable_Resources', 'Application_Model_DbTable_Acl');
        $roleMapper = new Application_Model_Mapper_Resources();
        return $roleMapper->createCollection($rowset);
    }
}