<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Model_Observer
{

    protected $collectionAdjusted = false;

    /**
     * @param $observer
     *
     * @throws Exception
     */
    public function addMassButton($observer)
    {
        Varien_Profiler::start(__METHOD__);
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
            || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
            || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_sales_orderManager_Grid_Massaction
        ) {
            if ($block->getRequest()->getControllerName() == 'sales_order'
                || $block->getRequest()->getControllerName() == 'adminhtml_sales_order'
            ) {
                $this->_addMassActions($block, Mage::app()->getStore()->isCurrentlySecure());
            }
        }
        Varien_Profiler::stop(__METHOD__);
    }

    /**
     * @param $block
     * @param $secure
     */
    protected function _addMassActions($block, $secure)
    {
        if (Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/invoiceall')
        ) {
            $block->addItem(
                'ordermanager_invoiceall',
                array(
                    'label' => Mage::helper('fooman_ordermanager')->__('Invoice Selected'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/invoiceall', $secure ? array('_secure' => 1) : array()
                    ),
                )
            );
        }

        if (Mage::helper('fooman_ordermanager')->getStoreConfig('shipaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/shipall')
        ) {
            $block->addItem(
                'ordermanager_shipall',
                array(
                    'label' => Mage::helper('fooman_ordermanager')->__('Ship Selected'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/shipall', $secure ? array('_secure' => 1) : array()
                    ),
                )
            );
        }

        if (Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceshipaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/invoiceandshipall')
        ) {
            $block->addItem(
                'ordermanager_invoiceandshipall',
                array(
                    'label' => Mage::helper('fooman_ordermanager')->__('Invoice + Ship Selected'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/invoiceandshipall',
                        $secure ? array('_secure' => 1) : array()
                    ),
                )
            );
        }

        if (Mage::helper('fooman_ordermanager')->getStoreConfig('statusaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/statusall')
        ) {
            $block->addItem(
                'ordermanager_statusall',
                array(
                    'label'      => Mage::helper('fooman_ordermanager')->__('Mass Status Update'),
                    'url'        => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/statusall', $secure ? array('_secure' => 1) : array()
                    ),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'status',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => Mage::helper('catalog')->__('Status'),
                            'values' => array_merge(
                                array('' => ''), $this->_getOrderStatuses()
                            )
                        )
                    )
                )
            );
        }

        if (Mage::helper('fooman_ordermanager')->getStoreConfig('emailinvoiceaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/emailinvoiceactionall')
        ) {
            $block->addItem(
                'ordermanager_emailinvoiceactionall',
                array(
                    'label' => Mage::helper('fooman_ordermanager')->__('Send Invoice Email'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/emailinvoiceall',
                        $secure ? array('_secure' => 1) : array()
                    ),
                )
            );
        }

        if (Mage::helper('fooman_ordermanager')->getStoreConfig('shipandsendinvoiceemailaction/enabled')
            && Mage::getSingleton('admin/session')->isAllowed('fooman_ordermanager/order/actions/shipandsendinvoiceemailall')
        ) {
            $block->addItem(
                'ordermanager_shipandsendinvoiceemailall',
                array(
                    'label' => Mage::helper('fooman_ordermanager')->__('Send Invoice Email + Ship Selected'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_orderManager/shipandsendinvoiceemailall',
                        $secure ? array('_secure' => 1) : array()
                    ),
                )
            );
        }

    }

    /**
     * retrieve configured order statuses, use config on older versions
     *
     * @return array
     */
    protected function _getOrderStatuses()
    {
        if (Mage::getSingleton('sales/order_status')->getCollection()) {
            return Mage::getSingleton('sales/order_status')->getCollection()->toOptionArray();
        }

        $returnArray = array();
        foreach (Mage::getModel('sales/order_config')->getStatuses() as $status => $statusLabel) {
            $returnArray[] = array('value' => $status, 'label' => $statusLabel);
        }
        return $returnArray;
    }

    /**
     * adjust the grid collection based on additional selected columns
     *
     * @param $observer
     */
    public function adjustGridCollection($observer)
    {
        Varien_Profiler::start(__METHOD__);
        if (!$this->collectionAdjusted) {
            $collection = $observer->getEvent()->getOrderGridCollection();
            if (!$collection->getIsCustomerMode()) {
                Mage::getResourceHelper('fooman_ordermanager')->addShippingInformation($collection);
                Mage::getResourceHelper('fooman_ordermanager')->addAddressFields($collection);
                $this->collectionAdjusted = true;
            }
        }
        Varien_Profiler::stop(__METHOD__);
    }

}

