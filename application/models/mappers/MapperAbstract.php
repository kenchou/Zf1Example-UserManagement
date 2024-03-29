<?php
/**
 * abstract mapper
 * this mapper based on db
 * @author Ken
 *
 */
class Application_Model_Mapper_MapperAbstract
{
    protected $_dbTableNamespace = 'Application_Model_DbTable';
    protected $_dbTableName;
    protected $_dbTableClass;
    protected $_modelClass;

    protected $_colsMap = array();
    protected $_dbTable;

    public function __construct()
    {
        //check necessary vars
        if (empty($this->_dbTableName)) {
            throw new RuntimeException(sprintf('need dbTableName in %s', get_class($this)));
        }
    }

    /**
     * load resource. mappers, dbTable, etc..
     * @param unknown $name
     * @param string $type
     * @throws RuntimeException
     */
    public function loadResource($name, $type = 'dbtable')
    {
        $name = ucfirst($name);
        $class = $this->_dbTableNamespace . '_' . $name;

        $resourceLoaders = Zend_Loader_Autoloader::getInstance()->getClassAutoloaders($class);
        foreach ($resourceLoaders as $loader) {
            return $loader->load($name, $type);
        }
        throw new RuntimeException('No loader for ' . $class);
    }

    /**
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable($this->_dbTableName);
        }
        return $this->_dbTable;
    }

    /**
     * set dbTable
     * @param unknown $dbTable
     * @return Application_Model_Mapper_MapperAbstract
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = $this->loadResource($dbTable);
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * create a new model
     * @param array $data
     * @return Application_Model_ModelAbstract
     */
    public function createModel($data = array(), $from = null)
    {
        $model = new $this->_modelClass();
        if ('db' == $from && $data) {
            $this->_colsToModel($model, $data);
        } else {
            $model->populate($data);
        }
        return $model;
    }

    /**
     * create resultset as  collection
     * @param array $resultset
     * @return array
     */
    public function createCollection($resultset)
    {
        $collection = new Application_Model_ModelCollection(array());
        foreach ($resultset as $data) {
            $collection[] = $this->createModel($data, 'db');
        }
        return $collection;
    }

    /**
     * mapping db col to model->property
     *
     * @param unknown_type $model
     * @param unknown_type $data
     */
    protected function _colsToModel($model, $data)
    {
        if (is_object($data) && is_callable(array($data, 'toArray'))) {
            $data = $data->toArray();
        }

        foreach ($this->_colsMap as $property => $col) {
            $callfunc = array($model, 'set' . $property);
            if (is_callable($callfunc)) {
                call_user_func($callfunc, isset($data[$col]) ? $data[$col] : null);
            } else {
                $model->$property = isset($data[$col]) ? $data[$col] : null;
            }
        }
        return $model;
    }

    /**
     * mapping model to db
     * @param unknown $data
     * @return multitype:unknown
     */
    protected function _modelToCols($data)
    {
        if (is_object($data) && is_callable(array($data, 'toArray'))) {
            $data = $data->toArray();
        }

        $result = array();
        foreach ($data as $property => $value) {
            if (isset($this->_colsMap[$property])) {
                $cols = $this->_colsMap[$property];
                $result[$cols] = $value;
            }
        }

        return $result;
    }

    /**
     *
     * Fetch row if primy key exists, otherwise create a row
     * @param array $data
     * @return Zend_Db_Table_Row_Abstract
     * @throws InvalidArgumentException
     */
    protected function _fetchRowOrCreate($data)
    {
        $row = $this->_fetchRow($data);
        if (null == $row) {
            $row = $this->getDbTable()->createRow();
        }
        return $row;
    }

    /**
     * fetch row from table
     *
     * @param array $data
     * @return Zend_Db_Table_Row_Abstract
     * @throws InvalidArgumentException
     */
    protected function _fetchRow($data)
    {
        $table = $this->getDbTable();
        $pk = $table->info('primary');
        if (!is_array($pk)) {
            $pk = (array) $pk;
        }

        $pkData = array();
        foreach ($pk as $fieldName) {
            if (array_key_exists($fieldName, $data)) {
                $pkData[] = $data[$fieldName];
            } elseif (!empty($pkData)) {
                throw new InvalidArgumentException("Could not find Primary Key part '$fieldName'");
            }
        }

        if (empty($pkData)) {
            return null;
        } else {
            $rowset = call_user_func_array(array($table , 'find'), $pkData);
            $row = $rowset->current();
        }
        return $row;
    }

    /**
     * Save model to db
     * @param Application_Model_ModelAbstract $model
     * @return Application_Model_Mapper_MapperAbstract
     */
    public function save(Application_Model_ModelAbstract $model)
    {
        $data = $this->_modelToCols($model);
        $row = $this->_fetchRowOrCreate($data);
        $row->setFromArray($data);
        $row->save();
        $this->_colsToModel($model, $row);//refresh data
        return $this;
    }

    /**
     * Find by PK
     * @param unknown $id
     * @return Ambigous <multitype:, Application_Model_ModelCollection, Application_Model_ModelAbstract>
     */
    public function find($id)
    {
        return $this->createCollection($this->getDbTable()->find($id));
    }

    /**
     * fetchAll record
     * @param string $select
     * @return Ambigous <multitype:, Application_Model_ModelCollection, Application_Model_ModelAbstract>
     */
    public function fetchAll($select = null)
    {
        $table = $this->getDbTable();

        return $this->createCollection($table->fetchAll($select));
    }

    /**
     * get SQL
     * @return Zend_Db_Table_Select
     */
    public function getSqlSelect()
    {
        return $this->getDbTable()->select();
    }

    /**
     * get db adapter
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->getDbTable()->getAdapter();
    }

    /**
     * magic call
     * handle:
     *     findBy{PropertyName}
     *     find{Resource}By{Model}  => call {Resource}Mapper::findBy{Model}($model)
     * @param unknown $method
     * @param unknown $params
     * @throws RuntimeException
     * @return Ambigous <multitype:, Application_Model_ModelCollection, Application_Model_ModelAbstract>
     */
    public function __call($method, $params)
    {
        if (preg_match('/find([A-Za-z0-9_]+)By([A-Za-z0-9_]+)/', $method, $matches)) {
            $resourceName = $matches[1];
            $method = 'findBy' . $matches[2];
            $resource = $this->loadResource($resourceName, 'mappers');
            return call_user_func_array(array($resource, $method), $params);
        }
        $magicMethodFindBy = 'findBy';
        if (false !== ($p = stripos($method, $magicMethodFindBy))) {
            $col = lcfirst(substr($method, $p+strlen($magicMethodFindBy)));
            if (isset($this->_colsMap[$col])) {
                $col = $this->_colsMap[$col];
            }
            $param = $params[0];
            $op = is_array($param) ? ' IN (?)' : ' = ?';
            $select = $this->getSqlSelect();
            $select->where($col . $op, $param);
            return $this->fetchAll($select);
        }
        throw new RuntimeException(sprintf('Call undefined method %s::%s', get_class($this), $method));
    }
}