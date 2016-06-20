<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Pquestion2
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Pquestion2_Block_Question_Form extends Mage_Core_Block_Template
{
    public function getAddQuestionUrl()
    {
        return Mage::getUrl(
            'aw_pq2/question/add', array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
        );
    }

    public function getProduct()
    {
        if (Mage::registry('current_product')) {
            return Mage::registry('current_product');
        }
        return Mage::getModel('catalog/product')->load(Mage::helper('aw_pq2/request')->getRewriteProductId());
    }

    public function canSpecifyVisibility()
    {
        return Mage::helper('aw_pq2/config')->getAllowCustomerDefinedQuestionVisibility();
    }
}