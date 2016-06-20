<?php
class Moogento_Pickpack_Model_Printgroup extends Mage_Core_Model_Abstract
{

    public function _construct() {
        parent::_construct();
        $this->_init('pickpack/printgroup');
    }
	
}