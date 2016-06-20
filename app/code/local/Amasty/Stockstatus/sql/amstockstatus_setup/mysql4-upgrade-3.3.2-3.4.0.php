<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
$installer = $this;
$installer->startSetup();

$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/displayforoutonly' 
                 WHERE `path` = 'catalog/general/displayforoutonly' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/displayincart' 
                 WHERE `path` = 'catalog/general/displayincart' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/userangesonly' 
                 WHERE `path` = 'catalog/general/userangesonly' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/icononly' 
                 WHERE `path` = 'catalog/general/icononly' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/stockalert' 
                 WHERE `path` = 'catalog/general/stockalert' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/outofstock' 
                 WHERE `path` = 'catalog/general/outofstock' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/alt_text' 
                 WHERE `path` = 'catalog/general/alt_text' ; 
                ");
$installer->run("
                UPDATE `{$this->getTable('core_config_data')}` 
                 SET `path` = 'amstockstatus/general/alt_text_loggedin' 
                 WHERE `path` = 'catalog/general/alt_text_loggedin' ; 
                ");

$installer->endSetup();