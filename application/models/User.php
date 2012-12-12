<?php
/**
 * Model Abstract
 * use Traits in php 5.4
 * @author Ken
 *
 */
class Application_Model_User extends Application_Model_ModelAbstract
{
    protected $_defaultResourceName = 'UserMapper';
    protected $_mapperClass = 'Application_Model_Mapper_UserMapper';

    public static $staticSalt = 'Kq6Dc%TX$2*xv3C^*jn$&gjXSfwPK8r^Q$E3faQpkuY9S6n4%8yYeshNMjXnT4ms';

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
        if (strlen($this->password)) {
            $this->salt = $this->salt(); //new salt if change password
            $this->password = $this->passwordHash($this->password,
                    $this->salt);
        }
Zend_Debug::dump($this, __METHOD__);
        $this->getMapper()->save($this);
        return $this;
    }
}