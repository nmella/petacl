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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_info_');
        $questionModel = Mage::registry('current_question');
        $fieldset = $form->addFieldset('general', array('legend' => $this->__('Question Details')));
        $fieldset->addField(
            'product_name',
            'link',
            array(
                 'label' => $this->__('Linked Entity'),
                 'title' => $questionModel->getProductName(),
                 'value' => $questionModel->getProductName(),
                 'href'  => $this->getUrl(
                     'adminhtml/catalog_product/edit',
                     array('id' => $questionModel->getProductId())
                 ),
                 'target' => '_blank'
            )
        );
        if (null === $questionModel->getId()) {
            $fieldset->addField(
                'store_id',
                'select',
                array(
                    'label'    => $this->__('Asked From'),
                    'name'     => 'store_id',
                    'type'     => 'store',
                    'required' => true,
                    'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                )
            );
        } else {
            $fieldset->addField(
                'store_label',
                'label',
                array(
                    'label' => $this->__('Asked From'),
                    'name'  => 'store_label',
                )
            );
        }
        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'created_at',
            'date',
            array(
                 'label' => $this->__('Created At'),
                 'name'  => 'created_at',
                 'title' => $this->__('Created At'),
                 'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                 'format' => $dateFormatIso,
                 'time'   => true
            )
        );
        if (Mage::app()->isSingleStoreMode()) {
            if (is_array($questionModel->getShowInStoreIds())) {
                $questionModel->setShowInStoreIds(implode(',', $questionModel->getShowInStoreIds()));
            }
            $fieldset->addField(
                'show_in_store_ids',
                'hidden',
                array(
                    'name' => 'show_in_store_ids[]'
                )
            );
        } else {
            $fieldset->addField(
                'show_in_store_ids',
                'multiselect',
                array(
                    'name'     => 'show_in_store_ids[]',
                    'label'    => $this->__('Show in stores'),
                    'title'    => $this->__('Show in stores'),
                    'required' => true,
                    'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                )
            );
        }
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => $this->__('Status'),
                'name'   => 'status',
                'type'   => 'select',
                'values' => Mage::getModel('aw_pq2/source_question_status')->toOptionArray()
            )
        );
        if (!$questionModel->getCustomerId()) {
            $fieldset->addField(
                'author_name',
                'text',
                array(
                    'label'    => $this->__('Author Name'),
                    'name'     => 'author_name',
                    'type'     => 'text',
                    'required' => true,
                )
            );
            $fieldset->addField(
                'author_email',
                'text',
                array(
                    'label'    => $this->__('Author Email'),
                    'name'     => 'author_email',
                    'type'     => 'text',
                    'required' => true,
                    'class'    => 'validate-email'
                )
            );
        } else {
            $authorLinkTitle = $questionModel->getAuthorName() . ' <' . $questionModel->getAuthorEmail(). '>';
            $questionModel->setAuthorLinkTitle($authorLinkTitle);
            $fieldset->addField(
                'author_link_title',
                'link',
                array(
                    'label' => $this->__('Author'),
                    'title' => $authorLinkTitle,
                    'value' => $authorLinkTitle,
                    'href'  => $this->getUrl(
                        'adminhtml/customer/edit',
                        array('id' => $questionModel->getCustomerId())
                    ),
                    'target' => '_blank'
                )
            );
        }
        $fieldset->addField(
            'content',
            'textarea',
            array(
                'label'    => $this->__('Question'),
                'name'     => 'content',
                'type'     => 'textarea',
                'required' => true,
            )
        );
        $fieldset->addField(
            'visibility',
            'select',
            array(
                'label'  => $this->__('Visibility'),
                'name'   => 'visibility',
                'type'   => 'select',
                'values' => Mage::getModel('aw_pq2/source_question_visibility')->toOptionArray()
            )
        );
        $fieldset->addField(
            'helpfulness',
            'text',
            array(
                 'label' => $this->__('Helpfulness'),
                 'name'  => 'helpfulness',
                 'type'  => 'text',
            )
        );
        $form->setValues($questionModel->getData());
        if ($questionModel->getCreatedAt()) {
            $form->getElement('created_at')->setValue(
                Mage::app()->getLocale()->date($questionModel->getCreatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }
        $this->setForm($form);
        return $this;
    }
}