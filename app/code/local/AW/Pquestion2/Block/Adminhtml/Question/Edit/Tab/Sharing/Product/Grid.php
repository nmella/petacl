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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Sharing_Product_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sharingProductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function setCollection($collection)
    {
        $collection->addAttributeToFilter(
            'visibility',
            array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
        );
        $questionModel = Mage::registry('current_question');
        $_sharingValue = $questionModel->getSharingValue();
        if ($questionModel->getSharingType() == AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE
            && count($_sharingValue) > 0
        ) {
            $collection->getSelect()
                ->order('(e.entity_id IN (' . implode(',', $questionModel->getSharingValue()) . ')) DESC')
            ;
        }
        return parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {

        $_result = parent::_prepareColumns();
        unset($this->_columns['action']);
        unset($this->_columns['qty']);
        unset($this->_columns['type']);
        unset($this->_columns['set_name']);
        unset($this->_columns['visibility']);
        unset($this->_columns['status']);
        $this->_exportTypes = array();
        $this->_rssLists = array();
        return $_result;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/sharingProductGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    protected function _prepareMassaction()
    {
        return parent::_prepareMassaction();
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getAfterHtml();
    }

    public function getAfterHtml()
    {
        $script = "
            if ($('sharingProductGrid_massaction-form')) {
                $('sharingProductGrid_massaction-form').remove();
            }
            if ($('sharingProductGrid_massaction-item-status-block')) {
                $('sharingProductGrid_massaction-item-status-block').remove();
            }
            if ($$('#sharingProductGrid_massaction .entry-edit').length > 0) {
                var entryEdit = $$('#sharingProductGrid_massaction .entry-edit').first();
                entryEdit.select('input').each(function(element) {
                    element.disabled = true;
                });
                entryEdit.select('.required-entry').each(function(element) {
                    element.disabled = true;
                });
                entryEdit.hide();
            }
        ";
        $questionModel = Mage::registry('current_question');
        $script .= "
            if ($$('#sharingProductGrid_table input.massaction-checkbox')
                && sharingProductGrid_massactionJsObject
            ) {
                var selectedProductsString = '" . implode(',', $questionModel->getSharingValue()) . "';
                sharingProductGrid_massactionJsObject.checkedString = selectedProductsString;
                var selectedProductsArray = selectedProductsString.split(',');
                for (var i =0; i < $$('#sharingProductGrid_table input.massaction-checkbox').length; i++) {
                    var el = $$('#sharingProductGrid_table input.massaction-checkbox')[i];
                    el.setAttribute('name','sharing_products[]');
                    if (selectedProductsArray.indexOf(el.value) != -1) {
                        el.checked = true;
                    }
                }
                sharingProductGrid_massactionJsObject.updateCount();
            }
        ";
        return "<script type='text/javascript'>" . $script . "</script>";
    }
}
