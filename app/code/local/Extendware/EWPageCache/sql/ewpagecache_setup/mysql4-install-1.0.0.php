<?php
Mage::helper('ewcore/cache')->clean();
$installer = $this;
$installer->startSetup();

try { 
	Mage::helper('ewpagecache/config')->reload()->saveConfigToFallbackStorage();
} catch (Exception $e) {
	Mage::logException($e);
}
$installer->endSetup();

// [[if normal]]
try {
	$incompatModules = array('Fooman_Speedster', 'GT_Speed', 'Magefox_Minify', 'Apptrian_Minify', 'Fooman_SpeedsterEnterprise', 'Fooman_SpeedsterAdvanced', 'Diglin_UIOptimization', 'Jemoon_Htmlminify', 'Nexcessnet_Turpentine', 'Brim_PageCache', 'VladimirPopov_WebForms', 'Wee_Fpc');
	foreach ($incompatModules as $module) {
		$model = Mage::getSingleton('ewcore/module');
		if (!$model) continue;
		
		$module = $model->load($module);
		if ($module->isActive() === false) continue;
		
		Mage::getModel('compiler/process')->registerIncludePath(false);
		$configTools = Mage::helper('ewcore/config_tools');
		if ($configTools) $configTools->disableModule($module->getId());
	}
} catch (Exception $e) {}
// [[/if]]