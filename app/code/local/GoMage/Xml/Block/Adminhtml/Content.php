<?php

class GoMage_Xml_Block_Adminhtml_Content extends Mage_Core_Block_Template {
	
	protected $_order = null;
	
	public function __construct() {
		parent::__construct();
		$this->setTemplate('gomage/xml/content.phtml');
	}
	
	public function getOrder() {		
		if (is_null($this->_order)){
			if (Mage::registry('current_order')){
				$this->_order = Mage::registry('current_order');
			}elseif ($this->getRequest()->getParam('order_id', false)){
				$this->_order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
			}
		}
				
		return $this->_order;
	}
	
	public function isFileGenerated(){
		$file_path = Mage::helper('gomage_xml')->getFilePath($this->getOrder()->getIncrementId());
		return file_exists($file_path);
	}
	
	public function getFileContent(){
		$content = '';
		$file_path = Mage::helper('gomage_xml')->getFilePath($this->getOrder()->getIncrementId());
		if (file_exists($file_path)){
			$content = file_get_contents($file_path);
		}
		return $content; 
	}

}