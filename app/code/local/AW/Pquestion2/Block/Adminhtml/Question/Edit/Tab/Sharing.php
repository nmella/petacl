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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Sharing extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_info_');
        $questionModel = Mage::registry('current_question');
        $fieldset = $form->addFieldset('sharing', array('legend' => $this->__('Sharing Details')));
        $fieldset->addField(
            'content',
            'label',
            array(
                'label' => $this->__('Question'),
                'name'  => 'content',
                'type'  => 'label',
            )
        );
        $fieldset->addField(
            'sharing_type',
            'select',
            array(
                'label'  => $this->__('Assigned Entity'),
                'name'   => 'sharing_type',
                'type'   => 'select',
                'values' => Mage::getModel('aw_pq2/source_question_sharing_type')->toOptionArray()
            )
        );
        $fieldset->addField(
            'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE,
            'note',
            array(
                'label' => $this->__('Select Product(s)'),
                'name'  => 'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE,
                'type'  => 'note',
                'text'  => $this->getLayout()
                    ->createBlock('aw_pq2/adminhtml_question_edit_tab_sharing_product_grid')->toHtml(),
            )
        );
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash()
        ;
        $fieldset->addField(
            'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE,
            'select',
            array(
                'label'  => $this->__('Select Attribute set'),
                'name'   => 'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE,
                'type'   => 'select',
                'values' => $sets
            )
        );
        $fieldset->addField(
            'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE,
            'select',
            array(
                'label'  => $this->__('Select Website'),
                'name'   => 'sharing_value_'.AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE,
                'type'   => 'select',
                'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
            )
        );
        $form->setValues($questionModel->getData());
        $this->setForm($form);
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($form->getHtmlIdPrefix() . 'sharing_type', 'sharing_type')
                ->addFieldMap(
                    $form->getHtmlIdPrefix() . 'sharing_value_'
                    . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE,
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE
                )
                ->addFieldMap(
                    $form->getHtmlIdPrefix() . 'sharing_value_'
                    . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE,
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE
                )
                ->addFieldMap(
                    $form->getHtmlIdPrefix() . 'sharing_value_'
                    . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE,
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE
                )
                ->addFieldDependence(
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE,
                    'sharing_type',
                    AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE
                )
                ->addFieldDependence(
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE,
                    'sharing_type',
                    AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE
                )
                ->addFieldDependence(
                    'sharing_value_' . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE,
                    'sharing_type',
                    AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE
                )
        );
        return $this;
    }
}