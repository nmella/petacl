<?php

try{
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

 $setup->addAttribute('order', 'shipment_id', array(
        'position'      => 1,
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => 'PluggTo Shipment Id',
        'visible'       => 0,
        'required'      => 0,
        'user_defined' => 1,
        'global'        => 1,
        'visible_on_front'  => 0,
    ));


// product
$codigo = 'pluggto_time';
$config = array(

				'type'     => 'int',
                'position' => 1,
                'required'=> 0,
                'label'    => 'Pluggto Update Time',
                "visible"  => false,
                'input'=>'text',
                'apply_to'=>'simple,bundle,grouped,configurable',
                'note'=>'Timestamp da modificação (Não modificar)',
                'group'=> 'PluggTo',
                'is_configurable'=> '0',
                'is_comparable'=>'0',
                'is_searchable'=>'0',
                'is_required'=>'0',
                'is_visible_on_front' => '0',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            );
$setup->addAttribute('catalog_product', $codigo , $config);			
// product
$codigo = 'pluggto_id';
$config = array(
				'type'     => 'varchar',
                'position' => 1,
                'required' => 0,
                'label'    => 'Pluggto Id',
                "visible"  => true,
                'input'    =>'text',
                'unique'   => true,
                'group'         => 'PluggTo',
                'is_configurable'=> '0',
                'is_comparable'=>'0',
                'is_searchable'=>'0',
                'is_required'=>'0',
                'is_visible_on_front' => '0',
                'apply_to' => 'simple,bundle,grouped,configurable',
                'note'     => 'ID do produto no pluggto',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            );

$setup->addAttribute('catalog_product', $codigo , $config);


$installer->run("

  DROP TABLE IF EXISTS `{$installer->getTable('pluggto/api')}`; 
    
  CREATE TABLE `{$installer->getTable('pluggto/api')}` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(245) NOT NULL,
  `expire` int(3) DEFAULT NULL ,
  `accesstoken` varchar(245) DEFAULT NULL,
  `refreshtoken` varchar(245) DEFAULT NULL,
  `line` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	
   DROP TABLE IF EXISTS `{$installer->getTable('pluggto/line')}`; 
  
  CREATE TABLE `{$installer->getTable('pluggto/line')}` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `what` varchar(12) NOT NULL,
  `storeid` varchar(245) DEFAULT NULL ,
  `status` int(1) DEFAULT '0' ,
  `pluggtoid` varchar(245) DEFAULT NULL ,
  `opt` varchar(4) DEFAULT NULL,
  `direction` varchar(10) DEFAULT NULL,
  `created` DATETIME DEFAULT NULL,
  `reason` varchar(12) NOT NULL,
  `attemps` int(2) DEFAULT NULL,
  `body` LONGTEXT DEFAULT NULL,
  `code` int(3) DEFAULT NULL,
  `result` LONGTEXT DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
		
 ");

Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pluggto')->__('O Pluggto foi instalado com successo'));

$installer->endSetup();

} catch (exception $e){
Mage::log(print_r($e,true));
Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pluggto')->__('A instalação do Pluggto falhou, verifique o log de erro.'));
} 