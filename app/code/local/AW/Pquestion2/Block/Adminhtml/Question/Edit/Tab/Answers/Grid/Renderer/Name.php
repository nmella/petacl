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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers_Grid_Renderer_Name
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $customerModel = Mage::getModel('customer/customer')->load($row->getCustomerId());
        if (null !== $customerModel->getId()) {
            return '<a href="' . $this->getUrl('adminhtml/customer/edit', array('id' => $customerModel->getId()))
                . '" target="_blank">' . $this->helper('aw_pq2')->stripTags($row->getAuthorName()) . '</a>';
        }
        return parent::render($row);
    }
}