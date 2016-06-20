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


class AW_Pquestion2_Block_Adminhtml_Question_Answer_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $answerModel = Mage::registry('current_answer');
        $id = Mage::app()->getRequest()->getParam('id');
        $customerId = Mage::app()->getRequest()->getParam('customer_id');
        $questionId = Mage::app()->getRequest()->getParam('question_id');
        $form = new Varien_Data_Form(
            array(
                'id'     => 'answer_edit_form',
                'action' => $this->getUrl(
                    'adminhtml/awpq2_answer/save',
                    array(
                        'id' => $id,
                        'question_id' => $questionId,
                        'customer_id' => $customerId
                    )
                ),
                'method' => 'post',
            )
        );
        if (!$answerModel->getCustomerId()) {
            $_authorName = new AW_Pquestion2_Block_Adminhtml_Question_Answer_Edit_Form_Element_Text(
                array(
                    'label'               => $this->__('Author Name'),
                    'name'                => 'author_name',
                    'type'                => 'text',
                    'after_element_html'  => '</span></span>',
                    'before_element_html' => '<span class="field-row"><span class="field-row">',
                    'required'            => true,
                )
            );
            $_authorName->setId('author_name');
            $form->addElement($_authorName);
            $_authorEmail = new AW_Pquestion2_Block_Adminhtml_Question_Answer_Edit_Form_Element_Text(
                array(
                    'label'               => $this->__('Author Email'),
                    'name'                => 'author_email',
                    'type'                => 'text',
                    'after_element_html'  => '</span></span>',
                    'before_element_html' => '<span class="field-row"><span class="field-row">',
                    'required'            => true,
                    'class'               => 'validate-email'
                )
            );
            $_authorEmail->setId('author_email');
            $form->addElement($_authorEmail);
        } else {
            $authorLinkTitle = $answerModel->getAuthorName() . ' <' . $answerModel->getAuthorEmail(). '>';
            $answerModel->setAuthorLinkTitle($authorLinkTitle);
            $_authorLinkTitleElement = new Varien_Data_Form_Element_Link(
                array(
                    'label' => $this->__('Author'),
                    'title' => $authorLinkTitle,
                    'value' => $authorLinkTitle,
                    'after_element_html' => '</span><div style="clear:both;"></div><span>',
                    'href'  => $this->getUrl(
                        'adminhtml/customer/edit',
                        array('id' => $answerModel->getCustomerId())
                    ),
                    'target' => '_blank'
                )
            );
            $_authorLinkTitleElement->setId('author_link_title');
            $form->addElement($_authorLinkTitleElement);
        }

        $_isAdminElement = new Varien_Data_Form_Element_Select(
            array(
                'label'  => $this->__('Is Admin\'s Answer'),
                'name'   => 'is_admin',
                'type'   => 'select',
                'after_element_html' => '<div style="clear:both"></div>',
                'values' => array(
                    1 => $this->__('Yes'),
                    0 => $this->__('No')
                )
            )
        );
        $_isAdminElement->setId('is_admin');
        $form->addElement($_isAdminElement);
        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $_createdAt = new Varien_Data_Form_Element_Date(
            array(
                'label'  => $this->__('Answer Date'),
                'name'   => 'created_at',
                'title'  => $this->__('Answer Date'),
                'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => $dateFormatIso,
                'time'   => true
            )
        );
        $_createdAt->setId('created_at');
        $form->addElement($_createdAt);
        $_statusElement = new Varien_Data_Form_Element_Select(
            array(
                'label'  => $this->__('Status'),
                'name'   => 'status',
                'type'   => 'select',
                'values' => Mage::getModel('aw_pq2/source_question_status')->toOptionArray()
            )
        );
        $_statusElement->setId('status');
        $form->addElement($_statusElement);
        $_helpfulnessElement = new Varien_Data_Form_Element_Text(
            array(
                'label' => $this->__('Helpfulness'),
                'name'  => 'helpfulness',
                'type'  => 'text',
                'after_element_html' => '<div style="clear:both"></div>'
            )
        );
        $_helpfulnessElement->setId('helpfulness');
        $form->addElement($_helpfulnessElement);
        $_contentElement = new Varien_Data_Form_Element_Textarea(
            array(
                'label'    => $this->__('Answer'),
                'name'     => 'content',
                'type'     => 'textarea',
                'required' => true,
            )
        );
        $_contentElement
            ->setRows(4)
            ->setCols(99)
            ->setId('content')
        ;
        $form->addElement($_contentElement);
        $form->setValues($answerModel->getData());
        if ($answerModel->getCreatedAt()) {
            $form->getElement('created_at')->setValue(
                Mage::app()->getLocale()->date($answerModel->getCreatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}