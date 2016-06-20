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
 * File        OrderController.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */


//require_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    const XML_PATH_EMAIL_TEMPLATE = 'sales_email/order/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'sales_email/order/guest_template';
    const XML_PATH_EMAIL_IDENTITY = 'sales_email/order/identity';
    const XML_PATH_EMAIL_COPY_TO = 'sales_email/order/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD = 'sales_email/order/copy_method';
    const XML_PATH_EMAIL_ENABLED = 'sales_email/order/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE = 'sales_email/order_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE = 'sales_email/order_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY = 'sales_email/order_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO = 'sales_email/order_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD = 'sales_email/order_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED = 'sales_email/order_comment/enabled';
    /**
     * Class Constructor
     * call the parent Constructor
     */

    private $include_orderid_yn;
    private $pdf_name;
    private $date_format;
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

	private function encoding_conv($var, $enc_out='ISO-8859-15', $enc_in='utf-8') {
		// ISO-8859-15 or UTF-8
	   $var = htmlentities($var, ENT_QUOTES, $enc_in);
	   return html_entity_decode($var, ENT_QUOTES, $enc_out);
	}
	
    //Order Grid Pdf Invoice
    public function mooinvoiceAction() {
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')) {
            if (is_null($orderIds)) {
                $orderIds = array();
                $orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
                $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                    ->getCollection()
                    ->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
                foreach ($collection as $item)
                    $orderIds[] = $item->getopp_order_id();
            }
        }
        $from_shipment = 'order';
        $text_orderId = '';
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (!empty($orderIds)) {
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
            //is shipeasy printing and manual printing is yes.
            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );

            //Default store config
            if (Mage::getStoreConfig("pickpack_options/wonder_invoice/additional_action_change_order_status_yn") == 1) {
                Mage::dispatchEvent(
                    'moo_pp_invoice_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
            Mage::helper('pickpack/print')->processPrint($orderIds, 'invoice');

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/invoice_pdf_name');

            if ($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Order Detail Pdf Invoice
    public function mooorderinvoiceAction() {

        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        /*get param invoice id if have*
        */
        $inovice_id = '';
        $shipment_ids = '';
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if ($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if ($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');

        $from_shipment = 'order';
        if (!empty($orderIds)) {
            if (($include_orderid_yn == 'yes' || $include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice', $inovice_id, $shipment_ids);

            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            Mage::helper('pickpack/print')->processPrint($orderIds, 'invoice');
            //Default store config
            if (Mage::getStoreConfig("pickpack_options/wonder_invoice/additional_action_change_order_status_yn") == 1) {
                Mage::dispatchEvent(
                    'moo_pp_invoice_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/invoice_pdf_name');
            if ($include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Order Grid Pdf Packing Sheet
    public function packAction() {
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')) {
            if (is_null($orderIds)) {
                $orderIds = array();
                $orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
                $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                    ->getCollection()
                    ->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
                foreach ($collection as $item)
                    $orderIds[] = $item->getopp_order_id();
            }
        }
        $from_shipment = 'order';
        $text_orderId = '';
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $orderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds)) {
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');

            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );

            //Default store config
            if (Mage::getStoreConfig("pickpack_options/wonder/additional_action_change_order_status_yn") == 1) {
                Mage::dispatchEvent(
                    'moo_pp_pack_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
            Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/packsheet_pdf_name');

            Varien_Profiler::start('PickPack PDF renderData');
            $renderData = $pdf->render();
            Varien_Profiler::stop('PickPack PDF renderData');


            $this->loadLayout();
            $this->renderLayout();

            if ($this->include_orderid_yn == 'yesdate') {
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $renderData, 'application/pdf');
            } elseif ($this->include_orderid_yn == 'yes') {
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $renderData, 'application/pdf');
            } else {
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $renderData, 'application/pdf');
            }
        }
        //$this->_redirect('*/*/');
    }


    //Order Detail Pdf Packing Sheet
    public function mooordershipmentAction() {
        $this->_assignOption();
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        $from_shipment = 'order';
        $inovice_id = '';
        $shipment_ids = '';
        /*get param invoice id if have**/
        if ($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if ($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');
        /*END*/
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if (!empty($orderIds)) {
            if (($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack', $inovice_id, $shipment_ids);

            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );

            //Default store config
            if (Mage::getStoreConfig("pickpack_options/wonder/additional_action_change_order_status_yn") == 1) {
                Mage::dispatchEvent(
                    'moo_pp_pack_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
            Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/packsheet_pdf_name');
            if ($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    //Invoice and Packing sheet
    public function mooinvoicepackAction() {

        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')) {
            if (is_null($orderIds)) {
                $orderIds = array();
                $orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
                $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                    ->getCollection()
                    ->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
                foreach ($collection as $item)
                    $orderIds[] = $item->getopp_order_id();
            }
        }
        $flag = false;
        $from_shipment = 'order';
        $text_orderId = '';
        //$include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/pack_invoice_pdf_name');
        $invoiceOrderIds = $orderIds;
        $packOrderIds = $orderIds;
        if (Mage::getStoreConfigFlag('pickpack_options/wonder_invoice/manual_processing_print_flag')) {
            $invoiceOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'invoice');
        }
        if (Mage::getStoreConfigFlag('pickpack_options/wonder/manual_processing_print_flag')) {
            $packOrderIds = Mage::helper('pickpack/print')->filterPrinted($orderIds, 'pack');
        }
        if (!empty($orderIds) && !empty($invoiceOrderIds) && !empty($packOrderIds)) {
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

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
                            $pdf->pages[] = $page;
                        }
                    }
                }

                if ($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                elseif ($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($invoiceOrderIds, $from_shipment, 'invoice');
                $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($packOrderIds, $from_shipment, 'pack');

                foreach ($pdfB->pages as $page) {
                    $pdfA->pages[] = $page;
                }

                if ($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
                elseif ($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdfA->render(), 'application/pdf');
            }
        }

        $this->_redirect('*/*/');
    }

    //Order Detail Pdf Invoice and Packing sheet
    public function mooorderinvoicepackAction() {

        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        /*get param invoice id if have*
        */
        $inovice_id = '';
        $shipment_ids = '';
        if ($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if ($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');

        $from_shipment = 'order';
        $storeId = Mage::app()->getStore()->getId();
        $option_group = 'general';
        $invoice_or_pack_first = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pdf_invoice_packing_sort', $storeId);
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if (!empty($orderIds)) {
            $methodName = 'getPdfDefault';
            if ($invoice_or_pack_first == 'invoice')
                $invoice_or_pack_second = 'pack';
            else
                $invoice_or_pack_second = 'invoice';
            $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, $invoice_or_pack_first, $inovice_id, $shipment_ids);
            $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, $invoice_or_pack_second, $inovice_id, $shipment_ids);
            foreach ($pdfB->pages as $page) {
                $pdfA->pages[] = $page;
            }
            Mage::helper('pickpack/print')->processPrint($orderIds, 'invoice');
            Mage::helper('pickpack/print')->processPrint($orderIds, 'pack');

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/pack_invoice_pdf_name');
            if (count($orderIds) == 1 && ($include_orderid_yn == 'yes' || $include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            if ($include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            elseif ($include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Separated Pick
    public function pickAction() {
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')) {
            if (is_null($orderIds)) {
                $orderIds = array();
                $orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
                $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                    ->getCollection()
                    ->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
                foreach ($collection as $item)
                    $orderIds[] = $item->getopp_order_id();
            }
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated($orderIds);

            Mage::helper('pickpack/print')->processPrint($orderIds, 'separate');
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/order_separated_picklist_name');
            if ($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Separated Pick 2
    public function pick2Action() {

        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated2')->getPickSeparated($orderIds);
            return $this->_prepareDownloadResponse('orders-summary_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Combined Pick
    public function enpickAction() {
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds, 'order_combined');
            Mage::helper('pickpack/print')->processPrint($orderIds, 'combined');
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/order_combined_picklist_name');
            if ($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Trolleybox Pick
    public function prodpickAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Address Labels
    public function labelAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/address_label_sheet_name');
            if ($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif ($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Gift Messages
    public function giftmessageAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_giftmessage')->getGiftmessage($orderIds);
            if (is_object($pdf)) {
                Mage::dispatchEvent(
                    'moo_pp_gift_message_pdf_generate_after',
                    array('order_ids' => $orderIds)
                );
                return $this->_prepareDownloadResponse('gift_message' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/sales_order/');
//         $this->_redirect('*/*/');
    }

    //Gift Cefiticate
    public function giftcetificateAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_giftcetificate')->getGiftcetificate($orderIds);
            if (is_object($pdf)) {
                Mage::dispatchEvent(
                    'moo_pp_gift_message_pdf_generate_after',
                    array('order_ids' => $orderIds)
                );
                return $this->_prepareDownloadResponse('gift_message' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/sales_order/');
    }

    //Address Labels
    public function labelzebraAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $only_print_once = Mage::getStoreConfig("pickpack_options/label_zebra/only_print_once");
        if (Mage::helper('pickpack')->isInstalled("Moogento_ShipEasy")) {
            if ($only_print_once == 1) {
                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                foreach ($orderIds as $key => $orderId) {
                    $print_yn = $resource->getValueColumnSe($orderId, "szy_custom_attribute3");
                    if ($print_yn != '') {
                        if (($key = array_search($orderId, $orderIds)) !== false) {
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

    public function labelzebradetailAction() {
        $orderId = array();
        if ($this->getRequest()->getParam('order_id'))
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

    //Out of stock pick list
    public function stockAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        $csv_or_pdf = Mage::getStoreConfig('pickpack_options/stock/csv_or_pdf');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
            if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();

            if ($csv_or_pdf == 'pdf') {
                $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/out_of_stock_list_name');
                if ($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                elseif ($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/out_of_stock_list_name');
                if ($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.csv', $pdf);
                elseif ($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.csv', $pdf);
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.csv', $pdf);
            }
        }
        $this->_redirect('*/*/');
    }

	private function outputCsvUtf8Bom($pdf,$fileName) {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Last-Modified: {$now} GMT");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=" . $fileName);
        header("Content-Transfer-Encoding: binary");
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        print($pdf);
        exit;
	}

	private function outputCsvAnsi($pdf,$fileName) {
		$pdf =  utf8_decode($pdf);
		// $pdf =  $this->encoding_conv($pdf); // alternative way to output ansi
		if(strpos($fileName,'.xml') !== false)
	    	return $this->_prepareDownloadResponse($fileName, $pdf);
		else
	    	return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
	}
	
	private function outputCsvHebrew($pdf,$fileName) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        // header("Content-Type: application/octet-stream");
        header("Content-Type: text/html; charset=UTF-16BE");
        header('Content-Disposition: attachment;filename=' . $fileName);
        header("Content-Transfer-Encoding: binary");
        print($pdf);
        exit;
	}

	private function outputCsvChinese($pdf,$fileName) {
        header('HTTP/1.1 200 OK');
        header('Date: ' . date('D M j G:i:s T Y'));
        header('Last-Modified: ' . date('D M j G:i:s T Y'));
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $fileName);
        print chr(255) . chr(254) . mb_convert_encoding($pdf, 'UTF-16LE', 'UTF-8');
        exit;
	}
	
	private function outputCsv($pdf,$fileName) {
		$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
		
        if ($this->generalConfig['non_standard_characters'] != 0) {
			if( ($this->generalConfig['non_standard_characters'] == 'simplified_chinese') || ($this->generalConfig['non_standard_characters'] == 'traditional_chinese'))
					$this->outputCsvChinese($pdf,$fileName);
            elseif ($this->generalConfig['non_standard_characters'] == 'hebrew')
					$this->outputCsvHebrew($pdf,$fileName);
		} else {
            if($csv_encoding == 'ansi')
				$this->outputCsvAnsi($pdf,$fileName);				
			else
                $this->outputCsvUtf8Bom($pdf,$fileName);
        }
	}
	
    //CSVOrders
    public function orderscsvAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');			
        if (empty($orderIds))
			$this->_redirect('*/*/');
	
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, false);
        $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
        $this->outputCsv($pdf,$fileName);
		return;
		
		/*
            To convert properly UTF8 data with EURO sign :
            iconv("UTF-8", "CP1252", $data)
        */
    }
	
    //CSV Separated     
    public function pickcsvAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');			
        if (empty($orderIds))
			$this->_redirect('*/*/');
	
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated($orderIds);
        $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
        $this->outputCsv($pdf,$fileName);
		return;
    }

    //CSV Combined
    public function pickcsvcombinedAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');			
        if (empty($orderIds))
			$this->_redirect('*/*/');
	
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'picklist');
        $fileName = 'pick-list-combined-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
        $this->outputCsv($pdf,$fileName);
		return;
    }

    //CSV Manifest
    public function manifestcsvcombinedAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');			
        if (empty($orderIds))
			$this->_redirect('*/*/');
	
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'manifest');
        if (Mage::getStoreConfig('pickpack_options/csvmanifestcombined/is_excel_yn') == 1)
            $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.xml';
		else
			$fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
		$this->outputCsv($pdf,$fileName);
		return;
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder() {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function sendNewOrderEmail($order) {
        $storeId = $order->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return false;
            return $order;
        }

        $emailSentAttributeValue = $order->hasEmailSent()
            ? $order->getEmailSent()
            : Mage::getModel('sales/order')->load($order->getId())->getData('email_sent');
        $order->setEmailSent((bool)$emailSentAttributeValue);
        // Get the destination email addresses to send copies to
        // $copyTo = $order->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        // $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        // if ($copyTo && $copyMethod == 'bcc') {
        //     // Add bcc to customer email
        //     foreach ($copyTo as $email) {
        //         $emailInfo->addBcc($email);
        //     }
        // }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        // if ($copyTo && $copyMethod == 'copy') {
        //     foreach ($copyTo as $email) {
        //         $emailInfo = Mage::getModel('core/email_info');
        //         $emailInfo->addTo($email);
        //         $mailer->addEmailInfo($emailInfo);
        //     }
        // }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order' => $order,
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();
        $order->setEmailSent(true);
        return true;
    }

    public function productSeparatedAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        if (!empty($orderIds)) {
            $product_filter = array();
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_productseparated')->getPdf($orderIds, 'order', $product_filter, 'manual');

            if (is_object($pdf)) {
                if (count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
                $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/product_separated_picklist_name');

                if ($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                elseif ($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }

    public function cn22Action() {
        if (!Mage::helper('pickpack')->isInstalled('Moogento_Cn22')) {
            $install_message = '</b>autoCN22 (Post Office customs declaration).</b> To enable this feature, please install <b><a href="https://moogento.com/magento-auto-cn22-customs-labels">Moogento autoCN22</a></b>';
            Mage::getSingleton('core/session')->addNotice($install_message);
            $this->_redirect('*/sales_order/');
        }

        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $product_filter = array();
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_cn22')->getPdf($orderIds, 'order', $product_filter, 'manual');
            if (is_object($pdf))
                return $this->_prepareDownloadResponse('cn22_label_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    public function trolleyboxAction() {
        if (!Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox')) {
            $install_message = '</b>Trolleybox Pick List Pdf.</b> To enable this feature, please install <b><a href="https://moogento.com/">Moogento Trolleybox</a></b>';
            Mage::getSingleton('core/session')->addNotice($install_message);
            $this->_redirect('*/sales_order/');
        }
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            //TODO packup
            // $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolleybox')->getPickCombined($orderIds, 'trolleybox');
            $pdf = Mage::getModel('trolleybox/pdf')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    private function _assignOption() {
        $this->include_orderid_yn = Mage::getStoreConfig('pickpack_options/file_name/include_orderid_filename');
        $this->date_format = Mage::getStoreConfig('pickpack_options/file_name/date_format');
    }
}
