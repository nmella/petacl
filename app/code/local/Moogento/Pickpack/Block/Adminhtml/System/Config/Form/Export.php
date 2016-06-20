<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Actions.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 
class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Export extends Mage_Adminhtml_Block_System_Config_Form_Field 
{
	/*
     * Set template
     */
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('moogento/pickpack/system/config/export.phtml');
	}
	/**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
	protected function _getElementhtml(Varien_Data_Form_Element_Abstract $element){
		return $this->_toHtml();
	}
	/**
     * Return ajax url for button
     *
     * @return string
     */
	public function getAjaxCheckUrl(){
		return Mage::helper('adminhtml')->getUrl('adminhtml/pickpack_action/export');
	}
	/**
     * Generate button html
     *
     * @return string
     */
	public function getButtonHtml(){
		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
		->setData(array(
			'id' => 'pickpackexport_button',
			'label' => $this->helper('adminhtml')->__('Export pickPack Settings'),
			'onclick' => 'javascript:exportPickpack(); return false;'
			));
		return $button->toHtml();
	}
}
?>