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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_info_answer_');
        $questionModel = Mage::registry('current_question');
        $_contentLabel = new AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers_Renderer_Question(
            array(
                'label' => $this->__('Question:'),
                'name'  => 'content',
                'type'  => 'label',
                'class' => 'answer-tab-question'
            )
        );
        $_answerGrid = new Varien_Data_Form_Element_Note(
            array(
                'type'  => 'note',
                'text'  => $this->getLayout()->createBlock('aw_pq2/adminhtml_question_edit_tab_answers_grid')->toHtml(),
            )
        );
        $_answerGrid->setId('answer_grid');
        $_contentLabel->setId('content');
        $form
            ->addElement($_contentLabel)
            ->addElement($_answerGrid)
        ;
        $form->setValues($questionModel->getData());
        $this->setForm($form);
        return $this;
    }
}