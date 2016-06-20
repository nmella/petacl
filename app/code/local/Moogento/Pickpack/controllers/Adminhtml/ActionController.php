<?php
class Moogento_Pickpack_Adminhtml_Pickpack_ActionController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }

    /**
     * Return some checking result
     *
     * @return void
     */
    public function resetAction() {
        $result = 0;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
		$tableName1 = $resource->getTableName('core_config_data');
		$tableName2 = $resource->getTableName('core_resource');

		$delete_core_resource = 'DELETE FROM '.$tableName2.' WHERE code like "moogento_pickpack_setup"';
		$delete_core_config_data = 'DELETE FROM '.$tableName1.' WHERE path like "%moogento_pickpack%"';

		$writeConnection->query($delete_core_resource);
		$writeConnection->query($delete_core_config_data);

		$query1 = 'SELECT * FROM '.$tableName1.' WHERE path like "%moogento_pickpack%" LIMIT 1';
		$data1  = $readConnection->fetchAll($query1);
		if(empty($data1))
		{
			echo 'Reset pickPack OK';
		}
		else
			echo 'Error resetting pickPack...';
		exit;
        Mage::app()->getResponse()->setBody($data1);
    }

    

    
}