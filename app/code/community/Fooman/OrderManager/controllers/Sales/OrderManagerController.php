<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Sales_OrderManagerController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'invoiceall':
                $aclResource = 'fooman_ordermanager/order/actions/invoiceall';
                break;
            case 'shipall':
                $aclResource = 'fooman_ordermanager/order/actions/shipall';
                break;
            case 'statusall':
                $aclResource = 'fooman_ordermanager/order/actions/statusall';
                break;
            case 'invoiceandshipall':
                $aclResource = 'fooman_ordermanager/order/actions/invoiceandshipall';
                break;
            case 'emailinvoiceall':
                $aclResource = 'fooman_ordermanager/order/actions/emailinvoiceall';
                break;
            case 'shipandsendinvoiceemailall':
                $aclResource = 'fooman_ordermanager/order/actions/shipandsendinvoiceemailall';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    /**
     * Invoice all selected orders
     */
    function invoiceallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process invoices
        $result = Mage::getModel('fooman_ordermanager/orderManager')->invoiceAll(
            $orderIds,
            Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceaction/newstatus'),
            Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceaction/sendemail')
        );

        //Add results to session
        $this->addResultsToSession($result, 'Invoiced');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'invoiceaction');
    }

    /**
     * Send Invoice Email for all selected orders
     */
    function emailinvoiceallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process invoices
        $result = Mage::getModel('fooman_ordermanager/orderManager')->emailInvoiceAll(
            $orderIds,
            Mage::helper('fooman_ordermanager')->getStoreConfig('emailinvoiceaction/newstatus')
        );

        //Add results to session
        $this->addResultsToSession($result, 'Emailed about Invoices');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'emailinvoiceaction');
    }

    /**
     * Send Invoice Email and Ship all selected orders
     */
    function shipandsendinvoiceemailallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process invoices
        $result = Mage::getModel('fooman_ordermanager/orderManager')->shipAndSendInvoiceEmailAll(
            $orderIds,
            Mage::helper('fooman_ordermanager')->getStoreConfig('shipandsendinvoiceemailaction/newstatus'),
            Mage::helper('fooman_ordermanager')->getStoreConfig('shipandsendinvoiceemailaction/sendemail'),
            $this->_getTrackingNumbers(),
            $this->_getCarrierCodes()
        );

        //Add results to session
        $this->addResultsToSession($result, 'Emailed about Invoices + Shipped');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'shipandsendinvoiceemailaction');
    }

    /**
     * Ship all selected orders
     */
    function shipallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process Shipments
        $result = Mage::getModel('fooman_ordermanager/orderManager')->shipAll(
            $orderIds,
            Mage::helper('fooman_ordermanager')->getStoreConfig('shipaction/newstatus'),
            Mage::helper('fooman_ordermanager')->getStoreConfig('shipaction/sendemail'),
            $this->_getTrackingNumbers(),
            $this->_getCarrierCodes()
        );

        //Add results to session
        $this->addResultsToSession($result, 'Shipped');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'shipaction');
    }

    /**
     * Change status of all selected orders
     */
    function statusallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process Update
        $result = Mage::getModel('fooman_ordermanager/orderManager')->statusAll(
            $orderIds,
            $this->getRequest()->getParam('status')
        );

        //Add results to session
        $this->addResultsToSession($result, 'Status changed');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'statusaction');

    }

    /**
     * Invoice and ship all selected orders
     */
    function invoiceandshipallAction()
    {
        $orderIds = $this->_getOrderIds();

        //Process Invoices + Shipments
        $result = Mage::getModel('fooman_ordermanager/orderManager')->invoiceAndShipAll(
            $orderIds,
            Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceshipaction/newstatus'),
            Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceshipaction/sendinvoiceemail'),
            Mage::helper('fooman_ordermanager')->getStoreConfig('invoiceshipaction/sendshipemail'),
            $this->_getTrackingNumbers(),
            $this->_getCarrierCodes()
        );

        //Add results to session
        $this->addResultsToSession($result, 'Invoiced and shipped');

        //go back to the order overview page
        $this->_redirectWithSelection($orderIds, 'invoiceshipaction');
    }

    /**
     * add both error and success message to admin session
     *
     * @param $result
     * @param $successMessage
     */
    public function addResultsToSession($result, $successMessage)
    {
        if (!empty($result['errors'])) {
            $this->_getSession()->addError(implode('<br/>', $result['errors']));
        }
        if (!empty($result['successes'])) {
            $this->_getSession()->addSuccess(
                Mage::helper('sales')->__($successMessage) . ': ' . implode(',', $result['successes'])
            );
        }
    }

    /**
     * sorted order ids from POST
     *
     * @return mixed
     */
    protected function _getOrderIds()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        sort($orderIds);
        return $orderIds;
    }

    /**
     * retrieve tracking numbers from POST
     * sort into array by order_id
     */
    protected function _getTrackingNumbers()
    {
        $trackingNumbersSorted = array();
        $trackingNumbersRaw = $this->getRequest()->getPost('tracking');
        if (!$trackingNumbersRaw) {
            return $trackingNumbersSorted;
        }
        $trackingNumbersRaw = explode(",", $trackingNumbersRaw);
        foreach ($trackingNumbersRaw as $trackingNumberRaw) {
            list($orderId, $number) = explode("|", $trackingNumberRaw);
            $trackingNumbersSorted[$orderId] = $number;
        }
        return $trackingNumbersSorted;
    }

    /**
     * retrieve carrier codes from POST
     * sort into array by order_id
     */
    protected function _getCarrierCodes()
    {
        $carrierCodesSorted = array();
        $carrierCodesRaw = explode(",", $this->getRequest()->getPost('carrier'));
        if (is_array($carrierCodesRaw)) {
            foreach ($carrierCodesRaw as $carrierCodeRaw) {
                if (!preg_match('/[a-z|]/', $carrierCodeRaw)) {
                    continue;
                }
                list($orderId, $code) = explode("|", $carrierCodeRaw);
                $carrierCodesSorted[$orderId] = $code;
            }
        }
        return $carrierCodesSorted;
    }

    /**
     * @param $orderIds
     * @param $action
     */
    protected function _redirectWithSelection($orderIds, $action)
    {
        $keepSelection = Mage::helper('fooman_ordermanager')->getStoreConfig($action . '/keepselection');
        if ($keepSelection && is_array($orderIds) && !empty($orderIds)) {
            $orderIds = implode(',', $orderIds);
            $this->_redirect('adminhtml/sales_order/', array('internal_order_ids' => $orderIds));
        } else {
            $this->_redirect('adminhtml/sales_order/');
        }
    }
}
