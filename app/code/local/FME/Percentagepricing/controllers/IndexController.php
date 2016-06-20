<?php
class FME_Percentagepricing_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/percentagepricing?id=15 
    	 *  or
    	 * http://site.com/percentagepricing/id/15 	
    	 */
    	/* 
		$percentagepricing_id = $this->getRequest()->getParam('id');

  		if($percentagepricing_id != null && $percentagepricing_id != '')	{
			$percentagepricing = Mage::getModel('percentagepricing/percentagepricing')->load($percentagepricing_id)->getData();
		} else {
			$percentagepricing = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($percentagepricing == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$percentagepricingTable = $resource->getTableName('percentagepricing');
			
			$select = $read->select()
			   ->from($percentagepricingTable,array('percentagepricing_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$percentagepricing = $read->fetchRow($select);
		}
		Mage::register('percentagepricing', $percentagepricing);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}