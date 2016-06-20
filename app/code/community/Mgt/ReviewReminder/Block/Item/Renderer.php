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

class Mgt_ReviewReminder_Block_Item_Renderer extends Mage_Core_Block_Template
{
    protected $_item;
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('mgt_review_reminder/item/renderer.phtml');
    }
    
    public function setItem(Mgt_ReviewReminder_Model_Reminder_Item $item)
    {
        $this->_item = $item;
    }
    
    public function getItem()
    {
        return $this->_item;
    }
    
    public function renderForm()
    {
        $layout = $this->getLayout();
        $form = $layout->createBlock('mgt_review_reminder/review_form');
        $form->setItem($this->getItem());
        return $form->toHtml();
    }
}