<?php
require_once ('Zend/Controller/Plugin/Abstract.php');
class Ken_Controller_Plugin_BenchmarkMemoryUsage extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Log
     */
    protected $_log = null;
    public function __construct (Zend_Log $log)
    {
        $this->_log = $log;
    }
    public function dispatchLoopShutdown ()
    {
        if (function_exists('memory_get_peak_usage')) {
            $peakUsage = memory_get_peak_usage(true);
            $url = $this->getRequest()->getRequestUri();
            $this->_log->debug(number_format($peakUsage) . ' bytes ' . $url);
        }
    }
}
