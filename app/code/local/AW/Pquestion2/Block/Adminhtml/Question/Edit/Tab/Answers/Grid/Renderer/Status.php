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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers_Grid_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    public function render(Varien_Object $row)
    {
        $html = '<select onchange="varienGridAction.execute(this);" name="'
            . ( $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId() )
            . '" ' . $this->getColumn()->getValidateClass() . '>'
        ;
        $value = $row->getData($this->getColumn()->getIndex());
        foreach ($this->getColumn()->getOptions() as $val => $label) {
            $selected = (($val == $value && (null !== $value)) ? ' selected="selected"' : '' );
            $html .= '<option ' . $this->_getOptionActionUrl($row->getId(), $val)
                . ' ' . $selected . '>' . $label . '</option>'
            ;
        }
        $html .= '</select>';
        return $html;
    }

    protected function _getOptionActionUrl($id, $value)
    {
        $href = array(
            'href' => "javascript:AnswerForm.open('"
                . $this->getUrl('*/awpq2_answer/changeStatus/', array('id' => $id, 'status' => $value))
                . "', '', '', 'answersGridJsObject.doFilter()', 570)"
            )
        ;
        $htmlAttributes = array('value' => $this->escapeHtml(Mage::helper('core')->jsonEncode($href)));
        $actionAttributes = new Varien_Object();
        $actionAttributes->setData($htmlAttributes);
        return $actionAttributes->serialize();
    }
}