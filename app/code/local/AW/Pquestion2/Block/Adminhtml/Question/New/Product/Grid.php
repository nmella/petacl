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


class AW_Pquestion2_Block_Adminhtml_Question_New_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
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
        return parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $_result = parent::_prepareColumns();
        unset($this->_columns['action']);
        $this->_exportTypes = array();
        $this->_rssLists = array();
        return $_result;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/new', array('product_id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        return $this;
    }

    public function getHeaderText()
    {
        return $this->__('Please Select a Product');
    }

    public function getBackButtonHtml()
    {
        return '';
    }
}