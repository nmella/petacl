<?php
$paths = array(
    dirname(dirname(dirname(dirname(__FILE__)))) . '/app/Mage.php',
    '../../../app/Mage.php',
    '../../app/Mage.php',
    '../app/Mage.php',
    'app/Mage.php',
);

foreach ($paths as $path) {
    if (file_exists($path)) {
        require $path; 
        break;
    }
}

Mage::app('admin')->setUseSessionInUrl(false);
error_reporting(E_ALL | E_STRICT);
if (file_exists(BP.DS.'maintenance.flag')) exit;
if (class_exists('Extendware') === false) exit;
if (Extendware::helper('ewpagecache') === false) exit;


if (Mage::helper('ewpagecache/lock')->lock('cron-cleaninventory') === false) {
	exit;
}

$currentTime = strtotime(now());
$file = Mage::helper('ewpagecache/internal_api')->getTmpDir() . DS . 'inventory.serialized';
if (is_file($file) === false) @file_put_contents($file, '');
if (is_writeable($file) === false) exit;
$lastData = @unserialize(file_get_contents($file));
if (is_array($lastData) === false) $lastData = array();

$toOutOfStock = array();
$toInStock = array();
$newProducts = array();

$newData = array();
$rows = Mage::getModel('cataloginventory/stock_item')->getCollection()->getData();
foreach ($rows as $row) {
	$newData[$row['item_id']] = $row;
	if (isset($lastData[$row['item_id']]) === false) {
		if (empty($lastData) === false) {
			$newProducts[] = $row['product_id'];
		}
	} else {
		$last = $lastData[$row['item_id']];
		if (($last['qty'] > 0 and $row['qty'] <= 0) or ($last['is_in_stock'] and !$row['is_in_stock'])) {
			$toOutOfStock[] = $row['product_id'];
		} elseif (($last['qty'] <= 0 and $row['qty'] > 0) or (!$last['is_in_stock'] and $row['is_in_stock'])) {
			$toInStock[] = $row['product_id'];
		}
	}
}

@file_put_contents($file, serialize($newData));

$tags = Mage::helper('ewpagecache/api')->getTagsForFlushFromProductIds($toOutOfStock);
Mage::helper('ewpagecache/api')->flushPagesMatchingAnyTag($tags);

// remove the "inventory" attribute if you do not want the parent categories to flush
$tags = Mage::helper('ewpagecache/api')->getTagsForFlushFromProductIds($newProducts, 'inventory');
Mage::helper('ewpagecache/api')->flushPagesMatchingAnyTag($tags);

$tags = Mage::helper('ewpagecache/api')->getTagsForFlushFromProductIds($toInStock, 'inventory');
Mage::helper('ewpagecache/api')->flushPagesMatchingAnyTag($tags);
				
Mage::helper('ewpagecache/lock')->unlock('cron-cleaninventory');
