<?php

class Moogento_Pickpack_Model_Adminhtml_Widget_Block_Observer extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _isAllowed($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }


    public function beforeHtml($observer)
    {
        $block = $observer->getBlock();
        $lucidpath_installed = Mage::helper('pickpack')->isInstalled("LucidPath_SalesRep");
        $magik_installed = Mage::helper('pickpack')->isInstalled("magik_magikfees");
        $Raveinfosys_Deleteorder = Mage::helper('pickpack')->isInstalled("Raveinfosys_Deleteorder");
        $MW_Ddate = Mage::helper('pickpack')->isInstalled("MW_Ddate");
        $Imedia_SalesOrder = Mage::helper('pickpack')->isInstalled("Imedia_SalesOrder");
        $Oscp_SalesOrderGridOverride = Mage::helper('pickpack')->isInstalled("Oscp_SalesOrderGridOverride");

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid
            || ((null !== $magik_installed) && $block instanceof Magik_Magikfees_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $lucidpath_installed) && $block instanceof LucidPath_SalesRep_Block_Adminhtml_Order_Grid)
            || ((null !== $Raveinfosys_Deleteorder) && $block instanceof Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $MW_Ddate) && $block instanceof MW_Ddate_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $Oscp_SalesOrderGridOverride) && $block instanceof Oscp_SalesOrderGridOverride_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $Imedia_SalesOrder) && $block instanceof Imedia_SalesOrder_Block_Sales_Order_Grid)
        ) {
            $default_massaction_items = $block->getMassactionBlock()->getItems();
            $massction_items = $block->getMassactionBlock();
            foreach ($default_massaction_items as $default_action) {
                switch ($default_action->getData('id')) {
                    case 'cancel_order':
                        if ($this->_getConfig('show_default_cancel_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('cancel_order');
                        }
                        break;
                    case 'hold_order':
                        if ($this->_getConfig('show_default_hold_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('hold_order');
                        }
                        break;
                    case 'unhold_order':
                        if ($this->_getConfig('show_default_unhold_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('unhold_order');
                        }
                        break;
                    case 'pdfinvoices_order':
                        if ($this->_getConfig('show_default_pdfinvoices_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('pdfinvoices_order');
                        }
                        break;
                    case 'pdfshipments_order':
                        if ($this->_getConfig('show_default_pdfshipments_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('pdfshipments_order');
                        }
                        break;
                    case 'pdfcreditmemos_order':
                        if ($this->_getConfig('show_default_pdfcreditmemos_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('pdfcreditmemos_order');
                        }
                        break;
                    case 'pdfdocs_order':
                        if ($this->_getConfig('show_default_pdfdocs_order', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('pdfdocs_order');
                        }
                        break;
                    case 'print_shipping_label':
                        if ($this->_getConfig('show_default_print_shipping_label', 1, false, 'action_menu') == 0) {
                            $block->getMassactionBlock()->removeItem('print_shipping_label');
                        }
                        break;

                }

            }
            if ($this->_getConfig('show_seperator1', 1, false, 'action_menu') == 1) {
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }
            if ($this->_isAllowed('moo_pickpack_pdf_packingsheet')) {
                if ($this->_getConfig('show_pdf_packing_sheet', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpack_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pack'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_invoice')) {
                if ($this->_getConfig('show_pdf_invoice', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfinvoice_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/mooinvoice'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_invoice_and_packingsheet')) {
                if ($this->_getConfig('show_pdf_invoice_and_packing_sheet', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfinvoice_pdfpack_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice & Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/mooinvoicepack'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_label_zebra')) {
                if ($this->_getConfig('show_pdf_label_zebra', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdflabel_zebra', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/labelzebra'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_address_label')) {
                if ($this->_getConfig('show_pdf_label_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdflabel_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Address Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/label'),
                    ));
                }
            }
            if (Mage::helper('pickpack')->isInstalled('Moogento_Cn22')) {
                if ($this->_isAllowed('moo_pickpack_pdf_cn22_label')) {
                    if ($this->_getConfig('show_pdf_label_cn22', 0, false, 'action_menu') == 1) {
                        $block->getMassactionBlock()->addItem('pdfcn22_order', array(
                            'label' => Mage::helper('pickpack')->__('PDF (CN22 Labels)'),
                            'url' => $block->getUrl('*/pickpack_sales_order/cn22'),
                        ));
                    }
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_combined')) {
                if ($this->_getConfig('show_pdf_enpick_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfenpick_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/enpick'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {
                if ($this->_getConfig('show_pdf_pick_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpick_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick'),
                    ));
                }
            }

            if (Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox')) {
                if ($this->_getConfig('show_pdf_trolleybox', 0, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdftrolleybox_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Trolleybox Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/trolleybox'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_product_separated')) {
                if ($this->_getConfig('show_pdf_product_separated', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfproduct_separated', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Product-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/productSeparated'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_separated2')) {
                if ($this->_getConfig('show_pdf_pick_order_2', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpick_order2', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick2'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_gift_message')) {
                if ($this->_getConfig('show_pdf_gift_message', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfgift_message', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Gift Message)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/giftmessage'),
                    ));
                }
            }

            // if ($this->_isAllowed('moo_pickpack_pdf_troylleybox')) {    
            if ($this->_isAllowed('moo_pickpack_pdf_csv_out_of_stock')) {
                if ($this->_getConfig('show_pdf_stock_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfstock_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF/CSV (Out-of-stock List)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/stock'),
                    ));
                }
            }

            if ($this->_getConfig('show_seperator2', 1, false, 'action_menu') == 1) {
                $block->getMassactionBlock()->addItem('seperator2', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }

            if ($this->_isAllowed('moo_pickpack_csv_orders')) {
                if ($this->_getConfig('show_csv_orders_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('csvorders_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Orders)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/orderscsv'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {
                if ($this->_getConfig('show_csv_pick_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('csvpick_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Order-separated Products)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pickcsv'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_combined_order')) {
                if ($this->_getConfig('show_csv_pickcombined_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('csvpickcombined_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Order-combined Products)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pickcsvcombined'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_manifest_combined_order')) {
                if ($this->_getConfig('show_manifest_combined_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('manifestcombined_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV/XML (Cargo Manifest)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/manifestcsvcombined'),
                    ));
                }
            }
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Shipment_Grid) {
            if ($this->_getConfig('show_seperator1', 1, false, 'action_menu') == 1) {
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {
                if ($this->_getConfig('show_pdf_packing_sheet', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpack_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/pack'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_label_zebra')) {
                if ($this->_getConfig('show_pdf_label_zebra', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdflabel_zebra_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/labelzebra'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {
                if ($this->_getConfig('show_pdf_pick_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpick_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/pick'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {
                if ($this->_getConfig('show_pdf_pick_order_2', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpick_order2', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick2'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_combined')) {
                if ($this->_getConfig('show_pdf_enpick_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfenpick_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/enpick'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_csv_out_of_stock')) {
                if ($this->_getConfig('show_pdf_stock_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfstock_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF/CSV (Out-of-stock List)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/stock'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_address_label')) {
                if ($this->_getConfig('show_pdf_label_order', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdflabel_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Address Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/label'),
                    ));
                }
            }
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Invoice_Grid) {

            if ($this->_getConfig('show_seperator1', 1, false, 'action_menu') == 1) {
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }

            if ($this->_isAllowed('moo_pickpack_pdf_invoice')) {
                if ($this->_getConfig('show_pdf_invoice', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfinvoice_invoice', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice)'),
                        'url' => $block->getUrl('*/pickpack_sales_invoice/mooinvoice'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {
                if ($this->_getConfig('show_pdf_packing_sheet', 1, false, 'action_menu') == 1) {
                    $block->getMassactionBlock()->addItem('pdfpack_invoice', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_invoice/pack'),
                    ));
                }
            }
        }
        // //for order view page
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View
            || $block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View
            || $block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View
        ) {


            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_button')) {
                $block->_addButton('PDF Invoice', array(
                        'label' => Mage::helper('sales')->__('PDF Invoice'),
                        'class' => 'pdf_invoice_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfInvoiceUrl() . '\')',
                    )
                );
            }

            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_packing_sheet_button')) {
                $block->_addButton('PDF Packing Ship', array(
                        'label' => Mage::helper('sales')->__('PDF Packing Sheet'),
                        'class' => 'pdf_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfShippingUrl() . '\')',
                    )
                );
            }

            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_and_packing_sheet_button')) {
                $block->_addButton('PDF Invoice & Packing', array(
                        'label' => Mage::helper('sales')->__('PDF Invoice and Packing Sheet'),
                        'class' => 'pdf_invoice_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfInvoiceShippingUrl() . '\')',
                    )
                );
            }

            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_zebra_label_button')) {
                $block->_addButton('Zebra Label', array(
                        'label' => Mage::helper('sales')->__('PDF Zebra Label'),
                        'class' => 'pdf_invoice_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfZebraLabelUrl() . '\')',
                    )
                );
            }

        }
        return $this;
    }

    private function _checkVersion()
    {
        $isVersionGt15 = true;
        $version_magento = Mage::getVersion();
        $versionArr = explode(".", $version_magento);
        if ($versionArr[0] < '1')
            $isVersionGt15 = false;
        elseif ($versionArr[0] == '1' && $versionArr[1] <= '5')
            $isVersionGt15 = false;
        return $isVersionGt15;
    }

    /**
     * Retrieve order model object
     *
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        $order = Mage::registry('sales_order');
        if (!$order) {
            $invoice = Mage::registry('current_invoice');
            if ($invoice) {
                $order = $invoice->getOrder();
            } else {
                $shipment = Mage::registry('current_shipment');
                if ($shipment) {
                    $order = $shipment->getOrder();
                }
            }
        }

        return $order;
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    protected function getOrderId()
    {
        $order = $this->getOrder();
        if ($order != null) {
            return $order->getId();
        } else {
            return null;
        }
    }

    public function getUrl($params = '', $params2 = array())
    {
        $params2['order_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    protected function getEditUrl()
    {
        return $this->getUrl('*/sales_order_edit/start');
    }

    public function getPdfInvoiceUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/mooorderinvoice/');
    }

    public function getPdfShippingUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/mooordershipment/');
    }

    public function getPdfZebraLabelUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/labelzebradetail/');
    }

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'action_menu', $store = null)
    {
        $value = trim(Mage::getStoreConfig('pickpack_options/' . $group . '/' . $field, $store));

        if (strstr($field, '_color') !== FALSE) {
            if ($value != 0 && $value != 1) {
                $value = checkColor($value);
            }
        }

        if ($value == '') {
            return $default;
        } else {
            if ($field == 'csv_field_separator' && $value == ',') return $value;
            // if(preg_match('~[a-zA-Z0-9]~',$value) === true && (strpos($value, ',') !== false) && (strpos($default, ',') !== false))// && (strpos($value, "\n")))
            if (($value !== '') && (strpos($value, ',') !== false) && (strpos($default, ',') !== false))// && (strpos($value, "\n")))
            {
                $values = explode(",", $value);
                $defaults = explode(",", $default);

                if ($add_default === true) {
                    $value = '';
                    $count = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        //if($value != '') $value .= ',';
                        if (($count != ($default_count)) && ($count != 0)) $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '') $value .= ($values[$i] + $defaults[$i]);
                        else $value .= $v;
                        $count++;
                    }
                } else {
                    $value = '';
                    $count = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        //if($value != '') $value .= ',';
                        if (($count != ($default_count)) && ($count != 0)) $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '') $value .= $values[$i];
                        else $value .= $v;
                        $count++;
                    }
                }
            } else {
                $value = ($add_default) ? ($value + $default) : $value;
            }
            return $value;
        }
    }

}