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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $actions[] = array(
            'url' => "javascript:AnswerForm.open('"
                . $this->getUrl('*/awpq2_answer/edit', array('id' => $row->getId()))
                . "', '', '" . Mage::helper('aw_pq2')->escapeHtml($this->__('Edit Answer')) . "', '', 570)"
            ,
            'caption' => $this->__('Edit')
        );
        $actions[] = array(
            'url' => "javascript:AnswerForm.open('"
                . $this->getUrl('*/awpq2_answer/delete', array('id' => $row->getId()))
                . "', '', '', 'answersGridJsObject.doFilter()', 570)"
        ,
            'confirm' => $this->__('Are you sure?'),
            'caption' => $this->__('Delete')
        );
        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}