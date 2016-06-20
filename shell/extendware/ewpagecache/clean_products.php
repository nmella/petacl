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

if (Mage::helper('ewpagecache/lock')->lock('cron-cleanproducts') === false) {
	exit;
}

$currentTime = strtotime(now());
$file = Mage::helper('ewpagecache/internal_api')->getTmpDir() . DS . 'last_pftime.txt';
$time = (int)@file_get_contents($file);
if ($time > 0) {
	$collection = Mage::getModel('catalog/product')->getCollection();
	$collection->getSelect()->where(sprintf('updated_at >= "%s"', date('Y-m-d H:i:s', $time-1)));
	$productIds = $collection->getAllIds();

	$tags = Mage::helper('ewpagecache/api')->getTagsForFlushFromProductIds($productIds);
	Mage::helper('ewpagecache/api')->flushPagesMatchingAnyTag($tags);
}
@file_put_contents($file, $currentTime);

Mage::helper('ewpagecache/lock')->unlock('cron-cleanproducts');