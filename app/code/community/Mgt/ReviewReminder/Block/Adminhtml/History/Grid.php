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

class Mgt_ReviewReminder_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('historyGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('reminder_history_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mgt_review_reminder_resource/history_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper =  Mage::helper('mgt_review_reminder');
        
        $this->addColumn('reminder_history_id', array(
            'header' => $helper->__('Id'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'reminder_history_id',
        ));
    
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'     => $helper->__('Store'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_view' => true,
                'width'  => '130px',
            ));
        }
        
        $this->addColumn('created_at', array(
            'header'    => $helper->__('Sent At'),
            'index'     => 'created_at',
            'type'      => 'datetime', 
            'gmtoffset' => true,
            'width'  => '100px',
        ));

        $this->addColumn('customer_name', array(
            'header'    => $helper->__('Customer Name'),
            'index'     => 'customer_name',
        ));
        
        $this->addColumn('customer_email', array(
            'header'    => $helper->__('Customer E-Mail'),
            'index'     => 'customer_email',
        ));

        $this->addColumn('reminder_sent', array(
            'header'    => $helper->__('Reminder Sent'),
            'index'     => 'reminder_sent',
            'width'  => '10px',
        ));

        return parent::_prepareColumns();
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }
    
    protected function _prepareMassaction()
    {
        $helper =  Mage::helper('mgt_review_reminder');
        
        $this->setMassactionIdField('reminder_history_id');
        $this->getMassactionBlock()->setFormFieldName('reminder_history');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $helper->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $helper->__('Are you sure to delete this reminder(s) history?')
        ));
    
        return $this; 
    }
}