<?php
/**
 * AdminUsers
 *
 * @author ken
 * @version
 */

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'users';
    protected $_primary = 'id';

    protected $_dependentTables = array('Application_Model_DbTable_UsersRoles');

}
