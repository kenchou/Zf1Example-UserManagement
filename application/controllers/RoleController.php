<?php
/**
 * AuthController
 *
 * @author
 * @version
 */
class RoleController extends Zend_Controller_Action
{
    public $applicationName = 'Role Management';

    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $this->view->user = Zend_Auth::getInstance()->getIdentity();
        $this->_forward('list');
    }

    /**
     * role list
     */
    public function listAction ()
    {
        $roleResource = $this->_helper->modelResource('Roles');
        $roles = $roleResource->fetchAll();
        $this->view->roles = $roles;
    }

    /**
     * edit role info
     */
    public function editAction()
    {
        $this->view->title .= ' - Edit Role';

        $id = $this->_getParam('id');
        if (empty($id)) throw new InvalidArgumentException('Missing parameter id.');

        $roleResource = $this->_helper->modelResource('Roles');
        $role = $roleResource->find($id)->getIterator()->current();

        $form = $this->_helper->form();
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();
            $role->populate($data);
            if (!$role->save()) {
                $this->_helper->flashMessenger('Cannot save data.');
            } else {
                $this->_helper->flashMessenger("save \"{$role->name}\" successfully.");
            }
        }
        $form->setDefaults($role->toArray());
        $form->setDefault('id', $role->id);

        $this->view->form = $form;
    }

    /**
     * add a role
     */
    public function addAction()
    {
        $this->view->title .= ' - Create Role';

        $form = $this->_helper->form('edit');
        $form->removeElement('id');

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();

            /* @var $roleResource Application_Model_Mapper_MapperAbstract */
            $roleResource = $this->_helper->modelResource('Roles');

            $role = $roleResource->findByName($data['name']);

            if (count($role)) {
                throw new RuntimeException("Cannot create role \"{$data['name']}\" already exists.");
            }

            $role = $roleResource->createModel();
            $role->populate($data);
            Zend_Debug::dump($role);

            if (!$role->save()) {
                throw new RuntimeException('Add Role failure!');
            }

            $this->_helper->flashMessenger("Add Role \"{$role->name}\" successful.");

            $this->_helper->redirector('list');
        }
        $this->view->form = $form;

        $this->render('edit');
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        if (empty($id)) throw new InvalidArgumentException('Missing parameter id.');

        $roleResource = $this->_helper->modelResource('Roles');
        $roles = $roleResource->find($id);

        foreach ($roles as $role) {
            $role->delete();
        }

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector('list');
    }

    /**
     * add Acl by role
     */
    public function authorizeAction()
    {
        $this->view->title .= ' - Grant Access';

        $id = $this->_getParam('roleId');
        if (empty($id)) throw new InvalidArgumentException('Missing parameter roleId.');

        // find current role
        $roleResource = new Application_Model_Mapper_Roles();
        /* @var $role Application_Model_Role */
        $role = current($roleResource->find($id));

        $Acl = new Application_Model_Mapper_Acl();

        $form = $this->_getForm('aclAdd');
        $form->setAction($form->getAction());

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $values = $form->getValues();
            $i = 0;
            $Acl->cleanByRoleAndResource($values['roleId'], $values['resourceId']);
            foreach ($values['action'] as $action) {
                $data = $values;
                $data['action'] = $action;

                $aclItem = $Acl->fetchRowByRoleAndResource($data['roleId'], $data['resourceId'], $action);
                $aclItem->setData($data);
                $aclItem->save();
            }
            $this->_helper->flashMessenger("Update $i rule(s) for role \"{$role->name}\"!");
            //clean acl cache
            $i = $this->_cleanAclCacheByRole($role);
            $this->_helper->flashMessenger("clean $i acl cache for {$role->name}.");
        }

        //get all registered resources
        $Resources = new Application_Model_Mapper_Resources();
        $rowset = $Resources->fetchAll();

        $allResources = array();
        foreach ($rowset as $resource) {
            $allResources[$resource->id] = $resource->name;
        }

        $form->setDefault('roleId', $role->id);
        //find resources by role
        $rowset = $role->getResources();
        $resources = array();
        foreach ($rowset as $resource) {
            $resources[$resource->id] = $resource;
        }
        //$aResources = array_diff_key($allResources, $resources);
        $rowset = $role->getAcl();
        $acl = array();
        foreach ($rowset as $item) {
            $acl[$item->resourceId][] = $item;
        }

        $form->getElement('resourceId')->setMultiOptions($allResources);

        $this->view->resources = $resources;
        $this->view->acl = $acl;
        $this->view->form = $form;

        $this->view->role = $role;
    }

    public function aclDeleteAction()
    {
        $roleId = $this->_getParam('RoleId');
        $resourceId = $this->_getParam('ResourceId');
        $action = $this->_getParam('Action');
        if (empty($roleId)) throw new InvalidArgumentException('Missing parameter RoleId.');
        if (empty($resourceId)) throw new InvalidArgumentException('Missing parameter ResourceId.');
        if (empty($action)) throw new InvalidArgumentException('Missing parameter Action.');

        $tableRoles = new Application_Model_Mapper_Roles();
        $role = $tableRoles->find($roleId)->current();

        $tableResources = new Application_Model_Mapper_Resources();
        $resource = $tableResources->find($resourceId)->current();

        $Acl = new Application_Model_Mapper_Acl();
        $row = $Acl->find($roleId, $resourceId, $action)->current();
        if (empty($row)) {
            throw new RuntimeException("Acl rule not found. (RoleId:$roleId, ResourceId:$resourceId, Action:$action)");
        }

        //clean acl cache
        $i = $this->_cleanAclCacheByRole($roleId);
        $this->_helper->flashMessenger("clean $i acl cache for {$roleId}");

        if (!$res = $row->delete()) {
            throw new RuntimeException("Cannot delete acl RoleId:$roleId, ResourceId:$resourceId, Action:$action");
        }

        $this->logOperation("Revoke $res ACL rule(s) for role:\"$role->name\" ($action) resource \"$resource->ResourceName\"");
        $this->_helper->flashMessenger("Delete $res rule(s).");

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector('view', 'role', 'admin', array('id' => $roleId));
    }

    protected function _cleanAclCacheByRoleId($role)
    {
        if (!$role instanceof Zend_Db_Table_Row) {
            $roleResource = new Application_Model_Mapper_Roles();
            $role = $roleResource->find($role)->current();
        }
        return $this->_cleanAclCacheByRole($role);
    }

    protected function _cleanAclCacheByRole($role)
    {
    	return false;
        if (!$role instanceof Zend_Db_Table_Row) {
            $roleResource = new Application_Model_Mapper_Roles();
            $role = $roleResource->fetchRow($roleResource->getAdapter()->quoteInto('name=?', $role));
            if (empty($role)) return false;
        }
        $cache = Zend_Registry::get('cache');
        $users = $role->findUsersViaUsersRoles();
        $i = 0;
        foreach ($users as $user) {
            $cacheId = 'thearchy_acl_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $user->Username) . '_hash' . md5($user->Username);;
            if ($cache->remove($cacheId)) {
                ++$i;
                $this->_logger->info('delete acl cache::' . $cacheId);
            }
        }
        return $i;
    }


}
