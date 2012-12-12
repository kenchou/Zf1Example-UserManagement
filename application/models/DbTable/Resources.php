<?php
/**
 * Resources
 *
 * @author ken
 * @version
 */
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_DbTable_Resources extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'resources';
    protected $_primary = 'id';

    protected $_dependentTables = array('Application_Model_DbTable_Acl');
}
