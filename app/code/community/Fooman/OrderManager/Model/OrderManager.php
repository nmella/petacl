<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Model_OrderManager
{

    /**
     * Invoice all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     *
     * @return array
     */
    public function invoiceAll($orderIds, $newOrderStatus = '', $sendEmail = false)
    {
        $successes = array();
        $errors = array();

        //loop through orders
        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    try {
                        $this->invoice($order, $newOrderStatus, $sendEmail);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }


    public function emailInvoiceAll($orderIds, $newOrderStatus = '')
    {
        $successes = array();
        $errors = array();

        //loop through orders
        if (is_array($orderIds) && !empty($orderIds)) {

            $collection = Mage::getModel('sales/order_invoice')->getCollection()
                ->addFieldToFilter('order_id', array('in' => $orderIds));
            if ($collection->getSize()) {
                foreach ($collection as $invoice) {
                    $invoiceIncrementId = $invoice->getIncrementId();
                    try {
                        $invoice->sendEmail();
                        $invoice->setEmailSent(true);
                        $invoice->getResource()->saveAttribute($invoice, 'email_sent');
                        if ($newOrderStatus) {
                            $this->changeOrderStatus($invoice->getOrder(), $newOrderStatus, true);
                        }
                        $successes[] = $invoiceIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $invoiceIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $invoiceIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function shipAll(
        $orderIds, $newOrderStatus = '', $sendEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array()
    ) {

        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                    $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                    try {
                        $this->ship($order, $newOrderStatus, $sendEmail, $trackingNrs, $carrierCode);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }

        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    public function shipAndSendInvoiceEmailAll(
        $orderIds, $newOrderStatus = '', $sendEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array()
    ) {

        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    try {
                        $invoices = $order->getInvoiceCollection();
                        if ($invoices) {
                            foreach ($invoices as $invoice) {
                                $invoice->sendEmail();
                                $invoice->setEmailSent(true);
                                $invoice->getResource()->saveAttribute($invoice, 'email_sent');
                            }
                        }

                        $orderIncrementId = $order->getIncrementId();
                        $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                        $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                        $this->ship($order, $newOrderStatus, $sendEmail, $trackingNrs, $carrierCode);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }

        }
        return array('errors' => $errors, 'successes' => $successes);
    }


    /**
     * Change Status on all selected orders
     *
     * @param $orderIds
     * @param $newOrderStatus
     *
     * @return array
     */
    public function statusAll($orderIds, $newOrderStatus)
    {
        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    try {
                        $this->changeOrderStatus($order, $newOrderStatus);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Capture all invoices for given orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     *
     * @return array
     */
    public function captureAll($orderIds, $newOrderStatus = '', $sendEmail = false)
    {
        $successes = array();
        $errors = array();

        //loop through orders
        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    try {
                        $this->invoice($order, $newOrderStatus, $sendEmail);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Invoice and ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendInvoiceEmail
     * @param bool   $sendShippingEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function invoiceAndShipAll(
        $orderIds, $newOrderStatus = '', $sendInvoiceEmail = false, $sendShippingEmail = false,
        $allTrackingNrs = array(), $allCarrierCodes = array()
    ) {

        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                    $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                    try {
                        $this->invoice($order, false, $sendInvoiceEmail);
                        $this->ship($order, $newOrderStatus, $sendShippingEmail, $trackingNrs, $carrierCode);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Invoice and capture all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     *
     * @return array
     */
    public function invoiceAndCaptureAll($orderIds, $newOrderStatus = '', $sendEmail = false)
    {
        $successes = array();
        $errors = array();

        //loop through orders
        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    try {
                        $this->invoice($order);
                        $this->capture($order, $newOrderStatus, $sendEmail);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Capture and ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function captureAndShipAll(
        $orderIds, $newOrderStatus = '', $sendEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array()
    ) {

        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                    $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                    try {
                        $this->capture($order);
                        $this->ship($order, $newOrderStatus, $sendEmail, $trackingNrs, $carrierCode);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * Invoice and ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function invoiceCaptureShipAll(
        $orderIds, $newOrderStatus = '', $sendEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array()
    ) {

        $successes = array();
        $errors = array();

        if (is_array($orderIds) && !empty($orderIds)) {
            $orders = $this->_getOrderCollection($orderIds);
            if ($orders->getSize()) {
                foreach ($orders as $order) {
                    $orderIncrementId = $order->getIncrementId();
                    $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                    $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                    try {
                        $this->invoice($order);
                        $this->capture($order);
                        $this->ship($order, $newOrderStatus, $sendEmail, $trackingNrs, $carrierCode);
                        $successes[] = $orderIncrementId;
                    } catch (Mage_Api_Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getCustomMessage();
                    } catch (Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }

    /**
     * @param $orderId
     * @param $carrierCodes
     *
     * @return mixed
     */
    public function getCarrierForOrder($orderId, $carrierCodes)
    {
        if (isset($carrierCodes[$orderId])) {
            return $carrierCodes[$orderId];
        } else {
            return Mage::helper('fooman_ordermanager')->getStoreConfig('settings/preselectedcarrier');
        }
    }

    /**
     * @param $orderId
     * @param $trackingNumbers
     *
     * @return array|bool
     */
    public function getTrackingNrsForOrder($orderId, $trackingNumbers)
    {
        if (!empty($trackingNumbers[$orderId])) {
            return explode(';', $trackingNumbers[$orderId]);
        }
        return false;
    }


    /**
     * Invoice all open items for a given order
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $newStatus
     * @param bool                   $email
     *
     * @return \Mage_Sales_Model_Order_Invoice
     */
    public function invoice(Mage_Sales_Model_Order $order, $newStatus = false, $email = false)
    {
        $service = $this->_getServiceModel($order);
        $invoice = $service->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $order->setIsInProcess(true);

        if (!$newStatus) {
            $order->addStatusToHistory(false, Mage::helper('fooman_ordermanager')->__('Created Invoice'), $email);
        }

        $this->_saveAsTransaction(array($order, $invoice));

        if ($email) {
            $invoice->sendEmail();
            $invoice->setEmailSent(true);
            $invoice->getResource()->saveAttribute($invoice, 'email_sent');
        }

        if ($newStatus) {
            $this->changeOrderStatus(
                $order, $newStatus, Mage::helper('fooman_ordermanager')->__('Created Invoice'), $email
            );
        }

        return $invoice;
    }

    /**
     * Ship all open items for a given order
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $newStatus
     * @param bool                   $email
     * @param array                  $trackingNrs
     * @param bool                   $carrier
     *
     * @return Mage_Sales_Model_Order_Shipment
     * @throws Exception
     */
    public function ship(
        Mage_Sales_Model_Order $order, $newStatus = false, $email = false, $trackingNrs = array(), $carrier = false
    ) {
        $service = $this->_getServiceModel($order);
        $shipment = $service->prepareShipment();
        $shipment->register();
        $order->setIsInProcess(true);
        if (!empty($trackingNrs)) {
            $this->_addTrackingInformation($shipment, $trackingNrs, $carrier);
        }

        $msg = Mage::helper('fooman_ordermanager')->__('Created Shipment') . ': ' . implode(',', $trackingNrs);
        if (!$newStatus) {
            $order->addStatusToHistory(false, $msg, $email);
        }

        $this->_saveAsTransaction(array($order, $shipment));

        if ($email) {
            $shipment->sendEmail();
            $shipment->setEmailSent(true);
            $shipment->getResource()->saveAttribute($shipment, 'email_sent');
        }

        if ($newStatus) {
            $this->changeOrderStatus(
                $order, $newStatus, $msg, $email
            );
        }

        return $shipment;
    }

    /**
     * Capture all invoices for a given order
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $newStatus
     * @param bool                   $email
     */
    public function capture(Mage_Sales_Model_Order $order, $newStatus = false, $email = false)
    {
        $invoices = $order->getInvoiceCollection();
        if ($invoices->getSize()) {

            foreach ($invoices as $invoice) {
                if ($invoice->canCapture()) {
                    $invoice->capture();
                    $order->setIsInProcess(true);
                    $this->_saveAsTransaction(array($order, $invoice));

                    if ($email) {
                        $invoice->sendEmail();
                        $invoice->setEmailSent(true);
                        $invoice->getResource()->saveAttribute($invoice, 'email_sent');
                    }
                }
            }
            if ($newStatus) {
                $this->changeOrderStatus(
                    $order, $newStatus, Mage::helper('fooman_ordermanager')->__('Captured Invoices')
                );
            }
        }
    }

    /**
     * Save all objects together in one transaction
     *
     * @param $objects
     *
     * @throws Exception
     * @throws bool
     */
    protected function _saveAsTransaction($objects)
    {
        $transaction = Mage::getModel('core/resource_transaction');
        foreach ($objects as $object) {
            $transaction->addObject($object);
        }
        $transaction->save();
    }

    /**
     * Update order to new status, optional comment
     *
     * @param Mage_Sales_Model_Order $order
     * @param                        $status
     * @param string                 $comment
     * @param bool                   $hasEmailBeenSent
     *
     * @throws Exception
     */
    public function changeOrderStatus(Mage_Sales_Model_Order $order, $status, $comment = '', $hasEmailBeenSent = false)
    {
        $order->addStatusToHistory($status, $comment, $hasEmailBeenSent);
        $order->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Service_Order
     */
    protected function _getServiceModel(Mage_Sales_Model_Order $order)
    {
        return Mage::getModel('sales/service_order', $order);
    }

    /**
     * @param $orderIds
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getOrderCollection($orderIds)
    {
        return Mage::getResourceModel('sales/order_collection')->addAttributeToFilter(
            'entity_id', array('in' => $orderIds)
        );
    }

    /**
     * @param $shipment
     * @param $trackingNrs
     * @param $carrierForOrder
     */
    protected function _addTrackingInformation(
        Mage_Sales_Model_Order_Shipment $shipment, array $trackingNrs, $carrierForOrder
    ) {

        $carrierTitle = Mage::helper('fooman_ordermanager')->getCarrierTitle($carrierForOrder);
        foreach ($trackingNrs as $trackingNr) {
            $track = Mage::getModel('sales/order_shipment_track')
                ->setNumber(trim($trackingNr))
                ->setCarrierCode($carrierForOrder)
                ->setTitle($carrierTitle);
            $shipment->addTrack($track);
        }
    }
}
