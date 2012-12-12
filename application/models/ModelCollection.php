<?php
/**
 * Model Collection (resultset)
 * use Traits in php 5.4
 * @author Ken
 *
 */
class Application_Model_ModelCollection extends ArrayObject
{
    /**
     * set array data
     *
     * @param array $data
     * @return Application_Model_ModelCollection
     */
    public function setFromArray($data)
    {
        $this->exchangeArray($data);
        return $this;
    }

    /**
     * export as array
     * @param string $recursion
     * @return array
     */
    public function toArray($recursion = false)
    {
        if ($recursion) {
            return array_map(function($item){
                return is_callable(array($item,'toArray')) ? $item->toArray(true) : $item;
            }, $this->getArrayCopy());
        } else {
            return $this->getArrayCopy();
        }
    }

    /**
     * merge another collection or array
     *
     * @param array|ArrayObject $data
     * @return Application_Model_ModelCollection
     */
    public function merge($data)
    {
        if (!$data instanceof ArrayObject && !is_array($data)) {
            throw new InvalidArgumentException(__METHOD__ . ' unsupport type. only accpet array or ArrayObject');
        }
        $data = array_merge($this->getArrayCopy(), (array)$data);
        $this->exchangeArray($data);
        return $this;
    }

    /**
     * extract value of property as list
     *
     * @param string $name
     * @return array
     */
    public function extractProperty($name)
    {
        $data = array();
        foreach ($this as $item) {
            if (isset($item[$name])) {
                $data[] = $item[$name];
            }
        }
        return $data;
    }

    /**
     * create a id => value mapping
     * @param string $id
     * @param string $value
     * @return array
     */
    public function createPropertiesMap($key, $value)
    {
        $data = array();
        foreach ($this as $item) {
            if (isset($item[$key])) {
                $k = $item[$key];
                $v = isset($item[$value]) ? $item[$value] : null;
                $data[$k] = $v;
            }
        }
        return $data;
    }
}