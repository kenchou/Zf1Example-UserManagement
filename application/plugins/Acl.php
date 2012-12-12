<?php
/**
 * Acl Plugin
 *
 */
class Application_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    const ALL_RESOURCES = '__ALL__';
    const ALL_ACTIONS = '__ALL__';
    const SESSION_NAMESPACE = 'PMA';

    /**
     * @var Zend_Acl
     */
    protected $_acl;
    /**
     * Zend Cache
     * @var Zend_Cache
     */
    protected $_cache;
    /**
     * @var Zend_Log
     */
    protected $_logger;
    /**
     * @var Application_Plugin_Acl_Adapter_Abstract
     */
    protected $_adapter;
    protected $_noAcl = array('index', 'index.index', 'error', 'error.forbidden', 'error.error' , 'user.login' , 'user.logout');

    public function getAdapter()
    {
        if (!$this->_adapter instanceof Application_Plugin_Acl_Adapter_Abstract) {
            $this->setAdapter('Application_Plugin_Acl_Adapter_Default');
        }
        return $this->_adapter;
    }

    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapter = new $adapter;
        }
        $this->_adapter = $adapter;
    }

    public function getResource($resource)
    {
        return Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource($resource);
    }

    public function getCache()
    {
        if (!$this->_cache instanceof Zend_Cache_Core) {
            $this->_cache = $this->getResource('cachemanager')->getCache('default');
        }
        return $this->_cache;
    }

    public function getLog()
    {
        if (!$this->_logger instanceof Zend_Log) {
            $this->_logger = $this->getResource('log');
        }
        return $this->_logger;
    }

    /**
     * Init ACL from database
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();

        $auth = Zend_Auth::getInstance();

        $authUser = $auth->getIdentity();

        if ($authUser) {
            $username = $authUser->username;
            $user = $authUser->userModel;
        } else {
            $username = null;
            $user = null;
        }

        //load acl cache
        $cache = $this->getCache();
        $cacheId = 'zfacl_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $username) . '_hash' . md5($username . $_SERVER['HTTP_HOST']);
        $acl = $cache->load($cacheId);

        if (!$acl || !$acl instanceof Zend_Acl) {
            /*Initial the acl component*/
            $this->getLog()->debug(sprintf('[%s]:Load ACL live! (%s)', __CLASS__, $cacheId));
            $acl = $this->_createAcl($user);
            $cache->save($acl, $cacheId);
        } else {
            $this->getLog()->debug(sprintf('[%s]:Load from cache. (%s)', __CLASS__, $cacheId));
        }

        $this->_acl = $acl;
        Zend_Registry::set('acl', $acl);

    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (!$this->_acl instanceof Zend_Acl) {
            return;
            throw new RuntimeException('ACL has not been initializated.');
        }
        // pass request to adapter
        /* @var $adapter Application_Plugin_Acl_Adapter_Default */
        $adapter = $this->getAdapter()->setRequest($request);
        $role      = $adapter->getRole();
        $resource  = $adapter->getResource();
        $privilege = $adapter->getPrivilege();

        try {
            if (! $this->_acl->has($resource)) {
                // using global resource
                $resource = null;
            }

            // if not dispatchable, then don't check acl.
            // from here something like NoRoute will take over
            $isDispatchable = Zend_Controller_Front::getInstance()->getDispatcher()->isDispatchable($this->getRequest());
            if ($isDispatchable && ! $this->_acl->isAllowed($role, $resource, $privilege)) {
                $adapter->notAllowed();
            }
        } catch (Exception $e) {
            $this->getLog()->debug($e);
            throw $e;
        }
    }

    /**
     * create acl live
     * @param Application_Model_User
     * @return Zend_Acl
     */
    protected function _createAcl($user)
    {
        $username = null;
        $parents = array();

        $acl = new Zend_Acl();

        try {
            //add resources
            $tableResources = new Application_Model_DbTable_Resources();
            $resources = $tableResources->fetchAll();
            foreach ($resources as $resource) {
                $acl->addResource($resource->resource_name);
            }

            if (!empty($user)) {
                $username = $user->username;

                $roles = $user->fetchRoles();

                //add Roles
                foreach ($roles as $role) {
                    $acl->addRole(new Zend_Acl_Role($role->name));
                    $parents[$role->id] = $role->name;
                }
                if (! empty($parents)) {
                    $aclRules = $user->fetchAclRules(array_keys($parents));
                    //add Resources and rules

                    foreach ($aclRules as $rule) {
                        $resource = (null == $rule->resourceName || 'anything' == $rule->resourceName) ? null : new Zend_Acl_Resource($rule->resourceName);

                        if (isset($resource) && !$acl->has($resource)) {
                            $acl->addResource($resource);
                        }

                        $action = (null == $rule->action || '__ALL__' == $rule->action) ? null : $action = $rule->action;

                        $role = $parents[$rule->roleId];
                        $permit = $rule->permit ? 'allow' : 'deny';
                        $acl->$permit($role, $resource, $action);
                    }
                }
            }
        } catch (Exception $e) {
			echo $e;exit;
            $this->_logger->err($e);
            throw $e;
        }

        //set my Role
        $myRole = new Zend_Acl_Role($username);
        $acl->addRole($myRole, $parents);

        //whitelist, internal resource
        if (!$acl->has('auth')) $acl->add(new Zend_Acl_Resource('auth'));
        if (!$acl->has('member')) $acl->add(new Zend_Acl_Resource('member'));
        if (!$acl->has('user')) $acl->add(new Zend_Acl_Resource('user'));
        if (!$acl->has('index')) $acl->add(new Zend_Acl_Resource('index'));
        if (!$acl->has('error')) $acl->add(new Zend_Acl_Resource('error'));

        //white list
        $acl->allow(null, 'user', 'index');
        $acl->allow(null, 'user', 'register');
        $acl->allow(null, 'user', 'passwd');

        $acl->allow(null, 'auth', 'login');
        $acl->allow(null, 'auth', 'logout');
        $acl->allow(null, 'member');
        $acl->allow(null, 'index');
        $acl->allow(null, 'error');

        return $acl;
    }
}