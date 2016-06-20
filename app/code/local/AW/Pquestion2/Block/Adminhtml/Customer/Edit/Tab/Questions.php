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


class AW_Pquestion2_Block_Adminhtml_Customer_Edit_Tab_Questions extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_pq2/customer/tab/question.phtml');
    }

    public function getTabLabel()
    {
        return Mage::helper('aw_pq2')->__('Product Questions');
    }

    public function getTabTitle()
    {
        return Mage::helper('aw_pq2')->__('Product Questions');
    }

    public function canShowTab()
    {
        return Mage::registry('current_customer') && null !== Mage::registry('current_customer')->getId();
    }

    public function isHidden()
    {
        return false;
    }

    public function getAfter()
    {
        return 'reviews';
    }

    public function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_aw_pq2');
        $customer = Mage::registry('current_customer');

        if ($customer->getWebsiteId() == 0) {
            $this->setForm($form);
            return $this;
        }

        $fieldset = $form->addFieldset(
            'base_fieldset', array('legend' => Mage::helper('aw_pq2')->__('Manage Notification List'))
        );

        $fieldset->addField(
            'subscribe_to', 'multiselect',
            array(
                'label'  => Mage::helper('aw_pq2')->__('Subscribe to'),
                'title'  => Mage::helper('aw_pq2')->__('Subscribe to'),
                'name'   => 'subscribe_to[]',
                'values' => Mage::helper('aw_pq2/notification')->getNotificationListForCustomer($customer)
            )
        );

        if ($customer->isReadonly()) {
            $form->getElement('subscribe_to')->setReadonly(true, true);
        }

        $this->setForm($form);
        return $this;
    }

    protected function _initFormValues()
    {
        $customer = Mage::registry('current_customer');
        $data = Mage::helper('aw_pq2/notification')->getNotificationListForCustomer(
            $customer, $customer->getWebsiteId()
        );
        $subscribeTo = array();
        foreach ($data as $item) {
            if ($item['checked']) {
                $subscribeTo[] = $item['value'];
            }
        }
        $this->getForm()->setValues(
            array(
                'subscribe_to' => $subscribeTo
            )
        );
        return $this;
    }
}