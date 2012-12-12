<?php
/**
 * A Zend Controller Plugin dedicated to undoing the damage of magic_quotes_gpc
 * in systems where it is on.
 *
 * @author  Ken
 * @version $Id: StripMagicQuotes.php,v 1.1 2009/04/23 07:30:57 ken_zhang Exp $
 */
require_once ('Zend/Controller/Plugin/Abstract.php');
class Ken_Controller_Plugin_StripMagicQuotes extends Zend_Controller_Plugin_Abstract
{
    /**
     * Called before the action loop is started. Will internally strip all
     * slashes off $request parameters
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $params = $request->getParams();
        array_walk_recursive($params, array($this , '_stripSlashes'));
        $request->setParams($params);

        if ($request instanceof Zend_Controller_Request_Http) {
            $this->_stripSlashesGPC();
        }
    }

    protected function _stripSlashesGPC()
    {
        array_walk_recursive($_GET, array($this , '_stripSlashes'));
        array_walk_recursive($_POST, array($this , '_stripSlashes'));
        array_walk_recursive($_COOKIE, array($this , '_stripSlashes'));
    }

    /**
     * Strip the slashes off an item in the Params array
     *
     * @param string $value
     * @param string $key
     */
    protected function _stripSlashes (&$value, $key)
    {
        $value = stripslashes($value);
    }
}