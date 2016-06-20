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


class AW_Pquestion2_Block_Adminhtml_Question_Answer_New_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'answer_new_form',
                'action' => $this->getUrl(
                    'adminhtml/awpq2_answer/new',
                    array(
                        'question_id' => Mage::app()->getRequest()->getParam('question_id'),
                    )
                ),
                'method' => 'post',
            )
        );

        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('aw_pq2/adminhtml_question_answer_new_edit_fieldset_renderer_element')
        );
        $fieldset = $form->addFieldset('customer_group_fieldset',
            array('legend' => $this->__('Please Select a Customer'))
        );
        $fieldset->addField(
            'customer_group',
            'radios',
            array(
                'label'     => $this->__('New answer from:'),
                'name'      => 'customer_group',
                'type'      => 'radios',
                'separator' => '<div style="clear:both"></div>',
                'values'    => Mage::getModel('aw_pq2/source_answer_customerGroup')->toOptionMultiArray()
            )
        );
        $fieldset->addField(
            'customer_grid',
            'note',
            array(
                'name'  => 'customer_grid',
                'type'  => 'note',
                'text'  => $this->getLayout()
                    ->createBlock('aw_pq2/adminhtml_question_answer_new_customer_grid')->toHtml(),
            )
        );
        $form->setValues(array('customer_group' => AW_Pquestion2_Model_Source_Answer_CustomerGroup::ADMIN_VALUE));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}