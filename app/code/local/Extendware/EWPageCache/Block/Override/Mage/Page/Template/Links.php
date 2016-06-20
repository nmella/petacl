<?php
class Extendware_EWPageCache_Block_Override_Mage_Page_Template_Links extends Extendware_EWPageCache_Block_Override_Mage_Page_Template_Links_Bridge
{
	public function addLink($label, $url='', $title='', $prepare=false, $urlParams=array(), $position=null, $liParams=null, $aParams=null, $beforeText='', $afterText='')
    {
    	if (Extendware::helper('ewpagecache')) {
	    	if (strpos($url, 'wishlist') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_wishlist') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_wishlist');
	        } elseif (strpos($url, 'checkout/cart') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_cart') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_cart');
	        } elseif (strpos($url, 'account/login') !== false or strpos($url, 'account/logout') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_login') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_login');
	        } elseif (strpos($url, 'checkout') !== false) {
	        	if (Mage::getDesign()->getPackageName() == 'rwd') {
		        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_checkout', array('was_logged_in' => Mage::getSingleton('customer/session')->isLoggedIn())) . $beforeText;
		        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_checkout');
	        	}
	        } elseif (strpos($url, 'create') !== false) {
	        	if (Mage::getDesign()->getPackageName() == 'rwd') {
		        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_register') . $beforeText;
		        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_register');
	        	}
	        }
    	}
        return parent::addLink($label, $url, $title, $prepare, $urlParams, $position, $liParams, $aParams, $beforeText, $afterText);
    }
    
	public function addLinkBlock($blockName)
    {
    	return parent::addLinkBlock($blockName);
    }
}
