<?php
/**
 * Model Abstract
 * use Traits in php 5.4
 * @author Ken
 *
 */
class Application_Model_User extends Application_Model_ModelAbstract
{
    const STATUS_NORMAL = 0;
    const STATUS_DISABLED = 1;
    const STATUS_UNVALIDATED = 2;

    protected $_status = array(
        self::STATUS_NORMAL => 'Normal',
        self::STATUS_DISABLED => 'Disabled',
        self::STATUS_UNVALIDATED => 'Unvalidated',
    );

    protected $_defaultResourceName = 'Users';
    protected $_mapperClass = 'Application_Model_Mapper_Users';

    public static $staticSalt = 'Kq6Dc%TX$2*xv3C^*jn$&gjXSfwPK8r^Q$E3faQpkuY9S6n4%8yYeshNMjXnT4ms';

    /**
     * get user status
     * @param int $code
     * @return string
     */
    public function getStatusText()
    {
        $code = $this->status;
        return isset($this->_status[$code]) ? $this->_status[$code] : 'Unknown';
    }

    /**
     * create random string as password salt
     *
     * @param int $minLength
     * @param int $maxLength
     */
    public function salt($minLength = 16, $maxLength = 32)
    {
        $string = '~!@#$%^&*()_+`1234567890-=qwertyuiop[]asdfghjkl;zxcvbnm,./QWERTYUIOP{}|ASDFGHJKL:ZXCVBNM<>?';
        $max = strlen($string) - 1;
        $saltLength = rand($minLength, $maxLength);
        $salt = '';
        for ($i = 0; $i < $saltLength; $i ++) {
            $p = rand(0, $max);
            $salt .= $string{$p};
        }
        return $salt;
    }

    /**
     * hash user password (for save to db)
     * @param string $password
     * @param string $salt
     */
    public function passwordHash($password, $salt, $raw = true)
    {
        return md5(self::$staticSalt . $password . $salt, $raw);
    }

    /**
     * save model data to storage
     */
    public function save ()
    {
        if (isset($this->password) && strlen($this->password)) {
            $this->salt = $this->salt(); //new salt if change password
            $this->password = $this->passwordHash($this->password,
                    $this->salt);
        }
        $this->getMapper()->save($this);
        return $this;
    }

    /**
     * find all roles of this user
     * @return Application_Model_ModelCollection
     */
    public function fetchRoles()
    {
        return $this->getMapper()->findRolesByUser($this->id);
    }

    public function fetchAclRules($roleIdList = array())
    {
        return $this->getMapper()->findAclByRole($roleIdList);
    }

}