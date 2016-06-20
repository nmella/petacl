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


class AW_Pquestion2_Block_Adminhtml_Question_New extends Mage_Adminhtml_Block_Template
{
    public function getHeaderHtml()
    {
        return '<h3 class="icon-head head-question">' . $this->getHeaderText() . '</h3>';
    }

    public function getHeaderText()
    {
        return $this->__('Create New Question');
    }

    public function getButtonsHeaderButtonsHtml()
    {
        $backButton = $this->getLayout()->createBlock('adminhtml/widget_button');
        $backButton->setData(
            array(
                 'label'   => Mage::helper('adminhtml')->__('Back'),
                 'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                 'class'   => 'back',
            )
        );
        return $backButton->toHtml();
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/list');
    }

    public function getGridHeaderText()
    {
        return $this->getChild('grid')->getHeaderText();
    }

    public function getGridHtml()
    {
        return $this->getChild('grid')->toHtml();
    }

    public function getGridHeaderButtonsHtml()
    {
        return $this->getChild('grid')->getGridHeaderButtonsHtml();
    }
}