<?php
class Extendware_EWPageCache_Model_Injector_Wishlist_Links extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$type = isset($params['type']) ? $params['type'] : 'wishlist/links';
		$block = Mage::app()->getLayout()->createBlock($type, $this->getId());
		if (isset($params['template'])) {
			$block->setTemplate($params['template']);
		}
		return $block->toHtml();
	}
}
