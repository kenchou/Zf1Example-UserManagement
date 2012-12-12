<?php
/**
 * System resource for ACL
 * @author Ken
 *
 */
class Application_Model_Mapper_Resources extends Application_Model_Mapper_MapperAbstract
{
    protected $_modelClass = 'Application_Model_Resource';
    protected $_dbTableClass = 'Application_Model_DbTable_Resources';
    protected $_dbTableName = 'Resources';

    protected $_colsMap = array(
        'id' => 'id',
        'name' => 'resource_name',
        'description' => 'description',
    );

    public function fetchRowByName($name)
    {
        $select = $this->getDbTable()->select();
        $select->where('resource_name=?', $name);
        return $this->fetchRow($select);
    }
}