<?php
/**
 * User
 * @author Ken
 *
 */
class Application_Model_Mapper_UserMapper extends Application_Model_Mapper_MapperAbstract
{
    protected $_modelClass = 'Application_Model_User';
    protected $_dbTableNamespace = 'Application_Model_DbTable';
    protected $_dbTableClass = 'Application_Model_DbTable_Users';
    protected $_dbTableName = 'Users';

    protected $_colsMap = array(
        'id'       => 'id',
        'username' => 'username',
        'password' => 'password',
        'realname' => 'realname',
        'email'    => 'email',
        'birthday' => 'birthday',
        'status'   => 'status',
        'salt'     => 'salt',
        'registerTime' => 'register_time',
        //'passwordHash' => 'password',
    );

    public function save(Application_Model_User $model)
    {
        $data = $this->_modelToCols($model);
        if (null === $model->password) {
            unset($data['password']);
        }
        if (null === $model->status) {
            $data['status'] = 0;
        }
        $row = $this->_fetchRowOrCreate($data);
        $row->setFromArray($data);
        $row->save();
        $this->_colsToModel($model, $row);//refresh data
        return $this;
    }
}