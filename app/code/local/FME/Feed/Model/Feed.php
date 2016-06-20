<?php

class FME_Feed_Model_Feed extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('feed/feed');
    }
    
    public function getRss()
    {
        if(Mage::getStoreConfig('feed/extension/enabled') =="1"):            
            $xml=simplexml_load_file("http://www.fmeextensions.com/feeds/extensions.xml");
            //read the xml
            foreach ($xml->entity as $value)
            {
                if($value->updates=="yes"):
                    $feedData[] = array(
                                    'severity' => (int)$value->severity,//2 for major,1 for criticle,4 for notice
                                    'date_added' => date('Y-m-d H:i:s'),
                                    'title' => (string)$value->title,
                                    'description' => (string)$value->discription,
                                    'url' => (string)$value->url,
                                        );
                    Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));              
                endif;
            }        
            Mage::app()->saveCache(time(), 'admin_notifications_lastcheck');
        endif;
    }
}