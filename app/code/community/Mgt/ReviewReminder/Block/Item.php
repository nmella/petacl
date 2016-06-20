<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_ReviewReminder
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_ReviewReminder_Block_Item extends Mage_Core_Block_Template
{
    const THUMBNAIL_IMAGE_WIDTH = 100;
    
    protected $_item;
    protected $_link;
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('mgt_review_reminder/item.phtml');
    }
    
    public function setItem(Mgt_ReviewReminder_Model_Reminder_Item $item)
    {
        $this->_item = $item;
    }
    
    public function getItem()
    {
        return $this->_item;
    }
    
    public function getName()
    {
        return $this->getItem()->getName();
    }
    
    public function getThumbnailHtml()
    {
        return $this->getItem()->getThumbnailHtml(self::THUMBNAIL_IMAGE_WIDTH);
    }
    
    public function getLink()
    {
        if (!$this->_link) {
            $item = $this->getItem();
            $reminder = $item->getReminder();
            $storeId = $reminder->getStoreId();
            $oldStore = Mage::app()->getStore();
            $store = Mage::app()->getStore($storeId);
            if ($store->getId()) {
                Mage::app()->setCurrentStore($store);
                $storeBaseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                $this->_link .= sprintf('%smgt/review/write/co/%s', $storeBaseUrl, $reminder->getCode());
                Mage::app()->setCurrentStore($oldStore);
            }
        }
        return $this->_link;
    }
}