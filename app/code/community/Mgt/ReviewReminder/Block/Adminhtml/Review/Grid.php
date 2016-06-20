<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_ReviewReminder
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_ReviewReminder_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Dashboard_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pendingReviewsGrid');
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('review/review');

        $collection = $model->getProductCollection()
          ->addStatusFilter($model->getPendingStatus())
          ->addStoreData();
          
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }
        
        $collection->setDateOrder()
                   ->setStoreId($storeId)
                   ->addStoreFilter($storeId)
                   ->addReviewSummary();

        foreach ($collection as $item) {
            if (!Mage::registry('review_data')) {
                Mage::register('review_data', $item);
            }
            $rating = $this->getLayout()->createBlock('adminhtml/review_rating_summary');
            $rating->setReviewId($item->getReviewId());
            $item->setData('summary_rating', $rating->toHtml());
        }

        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('reports')->__('Product Name'),
            'sortable'  => false,
            'index'     => 'name',
            'escape'    => true
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('reports')->__('Title'),
            'width'     => '200px',
            'sortable'  => false,
            'index'     => 'title',
            'truncate'  => 50,
            'escape'    => true
        ));

        $this->addColumn('summary_rating', array(
            'header'    => Mage::helper('review')->__('Summary Rating'),
            'width'     => '100px',
            'sortable'  => false,
            'index'     => 'summary_rating',
            'type'      => 'text',
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array('id'=>$row->getReviewId());
        if ($this->getRequest()->getParam('store')) {
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('adminhtml/catalog_product_review/edit', $params);
    }
}
