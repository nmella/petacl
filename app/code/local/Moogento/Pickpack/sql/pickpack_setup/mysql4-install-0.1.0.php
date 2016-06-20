<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        mysql4-install-0.1.0.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


$installer = $this;
$this->startSetup();

$this->getConnection()->resetDdlCache($this->getTable('sales/order'));

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'pp_invoice_printed',
    'tinyint(1) unsigned DEFAULT 0'
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'pp_packing_sheet_printed',
    'tinyint(1) unsigned DEFAULT 0'
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'pp_order_pick_list_printed',
    'tinyint(1) unsigned DEFAULT 0'
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'pp_items_pick_list_printed',
    'tinyint(1) unsigned DEFAULT 0'
);

$this->endSetup();
