<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        ShipmentController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

//require_once 'app/code/core/Mage/Adminhtml/controllers/Sales/ShipmentController.php';
$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/ShipmentController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_ShipmentController extends Mage_Adminhtml_Sales_ShipmentController
{
	private $generalConfig = array();

	protected function _construct() {
		$this->generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray(0);
	}
	
    protected function _isAllowed() {
        return true;
    }

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('sales/shipment')
            ->_addBreadcrumb($this->__('Shipment'), $this->__('Shipment')); //
        return $this;
    }

    public function indexAction() {
        parent::indexAction();
    }

    public function mooinvoiceAction() {
        $orderIds = $this->getRequest()->getPost('shipment_ids');
        $flag = false;
        $from_shipment = 'shipment';
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
            Mage::helper('pickpack/print')->processPrint($orderIds, 'invoice');
            return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    protected function _getOrderIds($shipment_ids) {
        $order_ids = array();
        foreach ($shipment_ids as $shipment_id) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
            $order_ids[] = $shipment->getOrderId();
        }
        return $order_ids;
    }

    public function packAction() {	
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));		
        $flag = false;
		$from_shipment = 'shipment'.'|'.implode(',',$this->getRequest()->getPost('shipment_ids'));
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');
            Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');
            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    public function labelzebraAction() {	
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));		
        $only_print_once = Mage::getStoreConfig("pickpack_options/label_zebra/only_print_once");
        if(Mage::helper('pickpack')->isInstalled("Moogento_ShipEasy")){
            if($only_print_once == 1){
                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                foreach ($orderIds as $key => $orderId) {
                    $print_yn = $resource->getValueColumnSe($orderId, "szy_custom_attribute3");
                    if($print_yn != ''){
                        if(($key = array_search($orderId, $orderIds)) !== false) {
                            unset($orderIds[$key]);
                        }
                    }
                }
                
            }
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra($orderIds);
            Mage::dispatchEvent(
                'moo_pp_zebra_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            return $this->_prepareDownloadResponse('zebra_labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Order Detail Pdf Invoice
    public function mooorderinvoiceAction() {
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        // get param invoice id if we have it
        $inovice_id = '';
        $shipment_ids = '';
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        // get param shipment id if we have it
        if($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');

        $from_shipment = 'order';
        if (!empty($orderIds)) {
            if(($include_orderid_yn == 'yes' || $include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice', $inovice_id, $shipment_ids);
            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            
            //Default store config
            if(Mage::getStoreConfig("pickpack_options/wonder_invoice/additional_action_change_order_status_yn") == 1) {
                Mage::dispatchEvent(
                    'moo_pp_invoice_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }

            if($include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse('invoice_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
	
    public function labelzebradetailAction() {
        $orderId = array();
        if($this->getRequest()->getParam('order_id'))
            $orderId[0] = $this->getRequest()->getParam('order_id');
        if (!empty($orderId)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra($orderId);
            Mage::dispatchEvent(
                'moo_pp_zebra_pdf_generate_after',
                array('order_ids' => $orderId)
            );
            return $this->_prepareDownloadResponse('zebra_labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
	
    public function moopackAction() {	
    	$shipment_arr = array();
    	$shipment_arr[] = $this->getRequest()->getParam('shipment_ids');
        $orderIds = $this->_getOrderIds($shipment_arr);		
        $flag = false;
		$from_shipment = 'shipment'.'|'.implode(',',$shipment_arr);
		$inovice_id = '';
		$shipment_ids = '';
		
		// get param invoice id if we have it
		if($this->getRequest()->getParam('invoice_id'))
			$inovice_id = $this->getRequest()->getParam('invoice_id');
		// get param shipment id if we have it
		if($this->getRequest()->getParam('shipment_ids'))
			$shipment_ids = $this->getRequest()->getParam('shipment_ids');
			/*END*/
			
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack', $inovice_id, $shipment_ids);
            Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');
            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function mooinvoicepackAction() {
       	$shipment_arr = array();
    	$shipment_arr[] = $this->getRequest()->getParam('shipment_ids');
        $orderIds = $this->_getOrderIds($shipment_arr);		
        $flag = false;
        $from_shipment = 'shipment'.'|'.$this->getRequest()->getParam('shipment_ids');

        $invoiceOrderIds = $orderIds;
        $packOrderIds = $orderIds;
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $invoiceOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $packOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds) && !empty($invoiceOrderIds) && !empty($packOrderIds)) {
            $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($invoiceOrderIds, $from_shipment, 'invoice');
            $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($packOrderIds, $from_shipment, 'pack');

            foreach ($pdfB->pages as $page) {
                $pdfA->pages[] = $page;
            }
            Mage::helper('pickpack/print')->processPrint($orderIds, array('invoice', 'pack'));
            return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function pickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated($orderIds);
            Mage::helper('pickpack/print')->processPrint($orderIds, 'separate');
            return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function enpickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds, 'order_combined');
            Mage::helper('pickpack/print')->processPrint($orderIds, 'combined');
            return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function prodpickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function stockAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function labelAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
            return $this->_prepareDownloadResponse('address-labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function orderscsvAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
		
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, false);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
			
            return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
		
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated2($orderIds);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
            return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
        }
        $this->_redirect('*/*/');
    }
}
