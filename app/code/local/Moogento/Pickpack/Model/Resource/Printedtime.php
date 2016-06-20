<?php 
class Moogento_Pickpack_Model_Resource_Printedtime extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('pickpack/printedtime', 'id');
    }
}