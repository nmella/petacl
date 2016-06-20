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


class AW_Pquestion2_Block_Question_Tab_List extends AW_Pquestion2_Block_Question_List
{
    protected function _construct()
    {
        $this->setTemplate('aw_pq2/catalog/product/view/list.phtml');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $sorter = $this->getLayout()
            ->createBlock('aw_pq2/question_sort', 'aw_pq2_question_sort')
            ->setTemplate('aw_pq2/question/sort.phtml');
        $questionForm = $this->getLayout()
            ->createBlock('aw_pq2/question_form', 'aw_pq2_ask_question_form')
            ->setTemplate('aw_pq2/question/form.phtml');
        $answerForm = $this->getLayout()
            ->createBlock('aw_pq2/answer_form', 'aw_pq2_add_answer_form')
            ->setTemplate('aw_pq2/answer/form.phtml');

        $this->setChild('aw_pq2_question_sort', $sorter);
        $this->setChild('aw_pq2_ask_question_form', $questionForm);
        $this->setChild('aw_pq2_add_answer_form', $answerForm);

        return parent::_prepareLayout();
    }

}