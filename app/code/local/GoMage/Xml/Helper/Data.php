<?php
class GoMage_Xml_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getFileName($order_id) {
		return $order_id . '.xml';
	}
	
	public function getFilePath($order_id) {
		$this->checkDir();
		$filename = $this->getFileName($order_id);
		return sprintf('%s/gomagexml/%s', Mage::getBaseDir('media'), $filename);
	}
	
	public function checkDir() {
		$file_dir = sprintf('%s/gomagexml', Mage::getBaseDir('media'));
		if (! file_exists($file_dir)) {
			mkdir($file_dir);
			chmod($file_dir, 0777);
		}
	}

}
	 