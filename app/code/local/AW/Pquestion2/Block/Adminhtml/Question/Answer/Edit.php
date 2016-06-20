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


class AW_Pquestion2_Block_Adminhtml_Question_Answer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_pq2';
        $this->_controller = 'adminhtml_question_answer';
        $this->_formScripts[] = "
            if ($('created_at_trig')) {
                Event.observe('created_at_trig', 'click', function(event) {
                    if (calendar != undefined) {
                        calendar.element.parentNode.replaceChild($(AnswerForm.dialogWindowId), calendar.element);
                        $(AnswerForm.dialogWindowId).appendChild(calendar.element);
                    }
                });
            }
        ";
        parent::__construct();
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->_addButton(
            'cancel',
            array(
                'label'   => $this->__('Cancel'),
                'onclick' => 'Windows.close(AnswerForm.dialogWindowId)',
            ),
            2
        );
        $this->updateButton(
            'save', 'onclick',
            "if (answerEditForm.validate()) {
                AnswerForm.open($(answerEditForm.formId).action,
                    $(answerEditForm.formId).serialize(),
                    '" . Mage::helper('aw_pq2')->escapeHtml($this->__('Edit Answer')) . "',
                    'answersGridJsObject.doFilter()',
                    570
                );
            }"
        );
    }

    public function getHeaderText()
    {
        return '';
    }
}