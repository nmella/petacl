<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$this->getTable('Servipag_api_debug')}`;
CREATE TABLE `{$this->getTable('Servipag_api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();

$installer->addAttribute('quote_payment', 'Servipag_payer_id', array());
$installer->addAttribute('quote_payment', 'Servipag_payer_status', array());
$installer->addAttribute('quote_payment', 'Servipag_correlation_id', array());
