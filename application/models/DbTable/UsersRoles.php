<?php
/**
 * relationship Users<->Roles
 *
 * @author ken
 * @version
 */
require_once 'Zend/Db/Table/Abstract.php';
class Application_Model_DbTable_UsersRoles extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'users_roles';
    protected $_primary = 'Id';

    protected $_referenceMap    = array(
        'User' => array(
            'columns'           => array('user_id'),
            'refTableClass'     => 'Application_Model_DbTable_AdminUsers',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT,
        ),
        'Role' => array(
            'columns'           => array('role_id'),
            'refTableClass'     => 'Application_Model_DbTable_Roles',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT,
        )
    );
}
