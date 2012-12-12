<?php
/**
 * ACL rule
 * relationship Roles<->Resources
 *
 * @author ken
 * @version
 */
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_DbTable_Acl extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'acl';
    protected $_primary = 'id';

    protected $_dependentTables = array('Application_Model_DbTable_Roles', 'Application_Model_DbTable_Resources');

    protected $_referenceMap    = array(
        'Role' => array(
            'columns'           => array('role_id'),
            'refTableClass'     => 'Application_Model_DbTable_Roles',
            'refColumns'        => array('id')
        ),
        'Resource' => array(
            'columns'           => array('resource_id'),
            'refTableClass'     => 'Application_Model_DbTable_Resources',
            'refColumns'        => array('id')
        )

    );
}
