<?php
/**
 * Roles
 *
 * @author ken
 * @version
 */
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_DbTable_Roles extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'roles';
    protected $_primary = 'id';

    protected $_dependentTables = array('Application_Model_DbTable_UsersRoles', 'Application_Model_DbTable_Acl');
}
