<?php
require_once ('Zend/Controller/Plugin/Abstract.php');
class Ken_Controller_Plugin_ETag extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $send_body = true;

        $etag = '"' . md5($this->getResponse()->getBody()) . '"';
        $this->getResponse()->setHeader("ETag", $etag, true);

        $inm = split(',', getenv("HTTP_IF_NONE_MATCH"));
        foreach ($inm as $i) {
          if (trim($i) == $etag) {
            $this->getResponse()->clearAllHeaders()
                 ->setHttpResponseCode(304)->clearBody();
            $send_body = false;
            break;
          }
        }

        if ($send_body) {
               $this->getResponse()
                         ->setRawHeader("Cache-Control: max-age=7200, must-revalidate, no-cache")
             ->setRawHeader("Expires: ". gmdate('D, d M Y H:i:s', time() + 7200) . ' GMT')
             ->setHeader("Content-Length", strlen($this->getResponse()->getBody()));
        }
    }
}
