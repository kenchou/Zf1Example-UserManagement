<?php
/**
 * ResourceController
 *
 * @author Ken Chou<kenchou77@gmail.com>
 * @version $:Id$
 */
class ResourceController extends Zend_Controller_Action
{
    public $applicationName = 'Resource Management';

    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $this->_forward('list');
    }

    /**
     * resource (in DB) list
     */
    public function listAction ()
    {
        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();

        $resourceResource = $this->_helper->modelResource('ResourceMapper');
        $resources = $resourceResource->fetchAll();
        $this->view->resources = $resources;
    }

    /**
     * Resource View
     */
    public function viewAction()
    {
        $this->view->title .= ' - View Resource';

        $id = $this->_request->getParam('id');
        if (empty($id)) {
            throw new RuntimeException('Missing parameter id.');
        }
        $resourceResource = $this->_helper->modelResource('ResourceMapper');
        $resource = $resourceResource->find($id)->current();

        if (empty($resource)) {
            throw new RuntimeException('Cannot found resource ' . $id);
        }
        $this->view->resource = $resource;

        //list roles by resource
        $roles = $resource->findRolesViaAcl();
    }

    /**
     * edit Resource
     * you can change Resource Name and description
     */
    public function editAction()
    {
        $this->view->title .= ' - Edit Resource';

        $id = $this->_getParam('id');
        if (empty($id)) {
            throw new RuntimeException('Missing parameter id.');
        }

        $resourceResource = $this->_helper->modelResource('ResourceMapper');
        $resource = $resourceResource->find($id)->current();
        if (empty($resource)) {
            throw new RuntimeException('Cannot found resource ' . $id);
        }

        $form = $this->_helper->form();
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();

            unset($data['id']);    //unset data for zf 1.6
            $resource->setData($data);
            if (!$resource->save()) {
                throw new RuntimeException('Save Resource failure!');
            }

            $this->logOperation("Edit resource($resource->Id):\"{$resource->name}\"");

            $this->_helper->flashMessenger("Save Resource \"{$resource->name}\" successful!");
            $this->view->messages = $this->_helper->flashMessenger->getCurrentMessages();
            $this->_helper->flashMessenger->clearCurrentMessages();
        }
        $form->setDefaults($resource->toArray());
        $form->setDefault('id', $resource->Id);
        $this->view->form = $form;
    }

    /**
     * Add a resource
     */
    public function addAction()
    {
        $this->view->title .= ' - Add Resource';

        $form = $this->_helper->form('edit');
        $form->removeElement('id');

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();

            /* @var $resourceResource Application_Model_Mapper_ResourceMapper */
            $resourceResource = $this->_helper->modelResource('ResourceMapper');

            $resource = $resourceResource->findByName($data['name']);
            if (count($resource)) {
                throw new RuntimeException("Cannot create role \"{$data['name']}\" already exists.");
            }

            $resource = $resourceResource->createModel();
            $resource->populate($data);

            if (!$resource->save()) {
                throw new RuntimeException('add Resource failure!');
            }

            $this->_helper->flashMessenger("add Resource \"{$resource->name}\" successfully!");

            $this->_helper->viewRenderer->setNoRender();
            //$this->_helper->redirector('list');
        }
        $this->view->form = $form;

        $this->render('edit');
    }

    /**
     * delete resource from DB
     */
    public function deleteAction()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            throw new RuntimeException('Missing parameter id.');
        }

        $resourceResource = $this->_helper->modelResource('ResourceMapper');

        $resources = $resourceResource->find($id);
        $logInfo = array();
        $i=0;
        foreach ($resources as $resource) {
            $logInfo[] = "\"{$resource->name}\" [Description]:\"{$resource->Description}\"";
            $resource->delete();
            ++$i;
        }
        $this->_helper->flashMessenger("delete $i resource(s).");
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector('list');
    }

    /**
     * list available resources in file system
     */
    public function discoverAction()
    {
        $this->view->title .= ' - Avaliable Resource';

        $data = array_values($this->_findResource());

        $this->view->data = $data;
    }

    /**
     * import all available resource to database
     */
    public function importAction()
    {
        $resourceResource = $this->_helper->modelResource('ResourceMapper');
        $resources = $resourceResource->fetchAll();

        $registered = array();
        foreach ($resources as $resource) {
            $registered[$resource->name] = $resource;
        }
        $srcResources = $this->_findResource();

        $i = 0;
        foreach ($srcResources as $k => $src) {
            if (!isset($registered[$k])) {
                $resource = $resourceResource->createModel();
                $resource->name = $src['resource'];
                $resource->description = $src['filename'] . ': ' . $src['actions'];
                $resource->save();
                ++$i;
            }
        }

        $this->_helper->flashMessenger("Import $i resource(s).");
        $this->_helper->redirector('list', 'resource', 'admin');
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * ajax: get Action of Resource (for role set ACL rules)
     */
    public function ajaxResourceActionAction()
    {
        $resourceId = $this->_getParam('resourceId');
        if (empty($resourceId)) {
            throw new InvalidArgumentException('missing param "resourceId".');
        }
        $Resource = $this->_helper->modelResource('ResourceMapper');
        /* @var $resource Application_Model_Resource */
        $resource = current($Resource->find($resourceId));

        $data = array('__ALL__');
        if ($this->_isController($resource->name)) {
            $resourceFile = $this->_getResourceFilename($resource->name);
            if (file_exists($resourceFile) && is_readable($resourceFile)) {
                $aResource = $this->_getResourceId($resourceFile);
                $data = array_merge($data, $aResource['action']);
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->ajaxContext();
            $this->_helper->json($data);
        } else {
            //$this->_helper->viewRenderer->setNoRender();
            $this->view->resource = $resource;
            $this->view->data = $data;
        }
    }

    protected function _isController($resource)
    {
        return preg_match('/^[a-z0-9]+_[a-z0-9]+$/i', $resource);
    }

    protected function _getResourceId($resource)
    {
        $resourceId = null;
        $code = php_strip_whitespace($resource);
        $matches = array();
        if (preg_match('/class\s+([a-z_][a-z_0-9]*)Controller\s+extends/i', $code, $matches)) {
            $resourceId = $matches[1];
            if (preg_match_all('/(?!protected|private)\s+function\s+([a-z_][a-z_0-9]*)Action\s*\(/i', $code, $matches)) {
                $actions = $matches[1];
            }

            return array('resource' => $resourceId, 'action' => $actions);
        }else {
            return false;
        }
    }

    protected function _getResourceFilename($resourceName)
    {
        $segments = explode('_', $resourceName);
        $module = strtolower($segments[0]);
        $controller = $segments[1];
        return implode(DIRECTORY_SEPARATOR, array($this->_getModuleDirectory($module), $controller . 'Controller.php'));
    }

    protected function _getModuleDirectory($module)
    {
        $controllerDirectory = Zend_Controller_Front::getInstance()->getControllerDirectory();
        return $controllerDirectory[$module];
    }

    protected function _findResource()
    {
        $controllerDirectory = Zend_Controller_Front::getInstance()->getControllerDirectory();

        $dir = new DirectoryIterator($controllerDirectory[$this->_request->getModuleName()]);

        $data = array();
        foreach ($dir as $file) {
            $filename = $file->getFilename();

            if (preg_match('/.+Controller.php$/', $filename)) {
                $resource = $this->_getResourceId($file->getPathname());
                if (!$resource) continue;

                $data[$resource['resource']] = array('resource' => $resource['resource'], 'actions' => implode(', ', $resource['action']), 'filename' => $filename);
            }
        }
        return $data;
    }
}
