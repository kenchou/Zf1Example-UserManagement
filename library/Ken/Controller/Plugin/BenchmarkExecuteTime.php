<?php
require_once ('Zend/Controller/Plugin/Abstract.php');
class Ken_Controller_Plugin_BenchmarkExecuteTime extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Log
     */
    protected $_log = null;
    protected $_benchmarkStartTime = 0;

    public function __construct($log = null)
    {
        $this->_benchmarkStartTime = microtime(true);
        $this->_log = $log;
    }

    public function getStartTime()
    {
        return $this->_benchmarkStartTime;
    }

    public function dispatchLoopShutdown()
    {
        $this->_log->debug(__METHOD__ . '() execute time:' . (microtime(true) - $this->_benchmarkStartTime));
    }
}
