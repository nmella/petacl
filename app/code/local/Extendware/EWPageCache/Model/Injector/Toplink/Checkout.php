<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Checkout extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());;
		$block->setWasLoggedIn((bool)$params['was_logged_in']);

		if (empty($params['template']) === true) {
			$params['template'] = 'extendware/ewpagecache/toplink/checkout.phtml';
		}
		$block->setTemplate($params['template']);
			
		return $block->toHtml();
	}
}
