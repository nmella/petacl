<?php
class FME_Feed_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/feed?id=15 
    	 *  or
    	 * http://site.com/feed/id/15 	
    	 */
    	/* 
		$feed_id = $this->getRequest()->getParam('id');

  		if($feed_id != null && $feed_id != '')	{
			$feed = Mage::getModel('feed/feed')->load($feed_id)->getData();
		} else {
			$feed = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($feed == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$feedTable = $resource->getTableName('feed');
			
			$select = $read->select()
			   ->from($feedTable,array('feed_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$feed = $read->fetchRow($select);
		}
		Mage::register('feed', $feed);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}