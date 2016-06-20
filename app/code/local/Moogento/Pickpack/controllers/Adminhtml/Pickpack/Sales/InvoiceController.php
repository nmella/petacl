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
* File        InvoiceController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

// require_once("Mage/Adminhtml/controllers/Sales/InvoiceController.php");
$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/InvoiceController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_InvoiceController extends Mage_Adminhtml_Sales_InvoiceController
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
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        return $this;
    }

    public function indexAction() {
        parent::indexAction();
    }

    protected function _getOrderIds($invoice_ids) {
        $order_ids = array();
        foreach ($invoice_ids as $invoice_id) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $order_ids[] = $invoice->getOrderId();
        }
        return $order_ids;
    }

    public function mooinvoiceAction() {
        $invoice_ids = array();
        $invoice_ids = $this->getRequest()->getPost('invoice_ids');
        if(!$invoice_ids){
            $invoice_ids[0] = $this->getRequest()->getParam('invoice_ids');
        }

        $orderIds = $this->_getOrderIds($invoice_ids);
        $from_shipment = 'order';
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            Mage::helper('pickpack/print')->processPrint($orderIds, 'invoice');
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

    public function packAction() {
        $invoice_ids = array();
        $invoice_ids = $this->getRequest()->getPost('invoice_ids');
        if(!$invoice_ids){
            $invoice_ids[0] = $this->getRequest()->getParam('invoice_ids');
        }

        $orderIds = $this->_getOrderIds($invoice_ids);
        $from_shipment = 'order';
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');
            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            if(!empty($pdf)) {
                Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');
            }

            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function mooinvoicepackAction() {
        $invoice_arr = array();
        $invoice_arr[] = $this->getRequest()->getParam('invoice_ids');
        $orderIds = $this->_getOrderIds($invoice_arr);     
        $flag = false;
        $from_shipment = 'invoice'.'|'.$this->getRequest()->getParam('invoice_ids');

        $invoiceOrderIds = $orderIds;
        $packOrderIds = $orderIds;
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $invoiceOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $packOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds)) {
            Mage::helper('pickpack/print')->processPrint($orderIds, array('pack', 'invoice'));
            if (Mage::getStoreConfig('pickpack_options/general/combined_orders_grouped_by_id_yn') == 1) {
                $pdf = new Zend_Pdf();

                foreach ($orderIds as $order_id) {
                    $order_id_array = array($order_id);
                    if (in_array($order_id, $invoiceOrderIds)) {
                        $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($order_id_array, $from_shipment, 'invoice');
                        foreach ($pdfA->pages as $page) {
                            $pdf->pages[] = $page;
                        }
                    }

                    if (in_array($order_id, $packOrderIds)) {
                        $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($order_id_array, $from_shipment, 'pack');
                        foreach ($pdfB->pages as $page) {
                            $pdfB->pages[] = $page;
                        }
                    }
                }
                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($invoiceOrderIds, $from_shipment, 'invoice');
                $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($packOrderIds, $from_shipment, 'pack');

                foreach ($pdfB->pages as $page) {
                    $pdfA->pages[] = $page;
                }
                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
            }
        }

        $this->_redirect('*/*/');
    }

    public function pickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated($orderIds);
            if(!empty($pdf)) {
                Mage::helper('pickpack/print')->processPrint($orderIds, 'separate');
            }
            return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function enpickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds, 'order_combined');
            if(!empty($pdf)) {
                Mage::helper('pickpack/print')->processPrint($orderIds, 'combined');
            }
            return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function prodpickAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    public function stockAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));
        $csv_or_pdf = Mage::getStoreConfig('pickpack_options/stock/csv_or_pdf');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
            if ($csv_or_pdf == 'pdf')
                return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            else {
				$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
				
				if($csv_encoding == 'ansi')
					$pdf =  utf8_decode($pdf);
                return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv', $pdf, 'text/csv');
            }
        }
        $this->_redirect('*/*/');
    }

    public function labelAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
            return $this->_prepareDownloadResponse('address-labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function orderscsvAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, 'order');
            $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');

			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated2($orderIds);
            $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvcombinedAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'picklist');
            $fileName = 'pick-list-combined-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi')
				$pdf =  utf8_decode($pdf);
            return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
        }
        $this->_redirect('*/*/');
    }

    public function manifestcsvcombinedAction() {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'manifest');

            if (Mage::getStoreConfig('pickpack_options/csvmanifestcombined/is_excel_yn') == 1) {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.xml';
                return $this->_prepareDownloadResponse($fileName, $pdf);
            } else {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
				$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');

				if($csv_encoding == 'ansi')
					$pdf =  utf8_decode($pdf);				
                return $this->_prepareDownloadResponse($fileName, $pdf, 'text/csv');
            }
        }
        $this->_redirect('*/*/');
    }
}
