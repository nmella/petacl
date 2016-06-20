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


class AW_Pquestion2_Model_Caching_Block_Question_List extends Enterprise_PageCache_Model_Container_Abstract
{
    protected function _getCacheId()
    {
        return 'AW_Pquestion2_Block_Question_List' . $this->_getIdentifier();
    }

    protected function _getIdentifier()
    {
        return microtime();
    }

    protected function _renderBlock()
    {
        $layout = Mage::app()->getLayout();
        $answerForm = $layout->createBlock('aw_pq2/answer_form', 'aw_pq2_add_answer_form');
        $answerForm->setTemplate('aw_pq2/answer/form.phtml');
        $questionListBlock = $layout->createBlock(
            $this->_placeholder->getAttribute('block'), $this->_placeholder->getAttribute('name')
        );
        $questionListBlock->setTemplate($this->_placeholder->getAttribute('template'));
        $questionFormBlock = $layout->createBlock('aw_pq2/question_form', 'aw_pq2_ask_question_form');
        $questionFormBlock->setTemplate('aw_pq2/question/form.phtml');
		$questionSortBlock = $layout->createBlock('aw_pq2/question_sort', 'aw_pq2_question_sort');
        $questionSortBlock->setTemplate('aw_pq2/question/sort.phtml');
        $questionListBlock
            ->setChild('aw_pq2_ask_question_form', $questionFormBlock)
            ->setChild('aw_pq2_add_answer_form', $answerForm)
			->setChild('aw_pq2_question_sort', $questionSortBlock)
        ;
        $questionListBlock->setLayout($layout);
        return $questionListBlock->toHtml();
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}